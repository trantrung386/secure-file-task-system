(function () {
    const cryptoApi = window.crypto && window.crypto.subtle;
    const app = window.E2E_APP || null;
    if (!cryptoApi || !app || !app.userId) {
        return;
    }

    const privateKeyStorage = `ttw:e2e:private:${app.userId}`;
    const publicKeyStorage = `ttw:e2e:public:${app.userId}`;

    const textEncoder = new TextEncoder();

    function bytesToBase64(bytes) {
        let binary = '';
        const chunkSize = 0x8000;
        for (let i = 0; i < bytes.length; i += chunkSize) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
        }
        return btoa(binary);
    }

    function base64ToBytes(value) {
        const binary = atob(value);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i += 1) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes;
    }

    async function getKeyPair() {
        const privateJwk = localStorage.getItem(privateKeyStorage);
        const publicJwk = localStorage.getItem(publicKeyStorage);
        if (privateJwk && publicJwk) {
            return {
                privateKey: await cryptoApi.importKey(
                    'jwk',
                    JSON.parse(privateJwk),
                    { name: 'RSA-OAEP', hash: 'SHA-256' },
                    true,
                    ['decrypt']
                ),
                publicJwk,
            };
        }

        const pair = await cryptoApi.generateKey(
            {
                name: 'RSA-OAEP',
                modulusLength: 2048,
                publicExponent: new Uint8Array([1, 0, 1]),
                hash: 'SHA-256',
            },
            true,
            ['encrypt', 'decrypt']
        );

        const exportedPrivate = JSON.stringify(await cryptoApi.exportKey('jwk', pair.privateKey));
        const exportedPublic = JSON.stringify(await cryptoApi.exportKey('jwk', pair.publicKey));
        localStorage.setItem(privateKeyStorage, exportedPrivate);
        localStorage.setItem(publicKeyStorage, exportedPublic);

        return {
            privateKey: pair.privateKey,
            publicJwk: exportedPublic,
        };
    }

    async function publishPublicKey() {
        const pair = await getKeyPair();
        await fetch(app.savePublicKeyUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ public_key: pair.publicJwk }),
        });
    }

    async function importRecipientPublicKey(publicJwk) {
        return cryptoApi.importKey(
            'jwk',
            JSON.parse(publicJwk),
            { name: 'RSA-OAEP', hash: 'SHA-256' },
            true,
            ['encrypt']
        );
    }

    function ensureHidden(form, name) {
        let input = form.querySelector(`input[name="${name}"]`);
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            form.appendChild(input);
        }
        return input;
    }

    function setStatus(element, message, type) {
        if (!element) return;
        element.textContent = message;
        element.classList.remove('alert-info', 'alert-danger', 'alert-success', 'd-none');
        element.classList.add(type === 'error' ? 'alert-danger' : type === 'success' ? 'alert-success' : 'alert-info');
    }

    function recipientsUrlForInput(input) {
        if (input.dataset.recipientUrl) {
            return input.dataset.recipientUrl;
        }

        const selector = input.dataset.projectSelect;
        if (selector) {
            const projectSelect = document.querySelector(selector);
            if (projectSelect && projectSelect.value) {
                return `${app.projectRecipientsUrl}&id=${encodeURIComponent(projectSelect.value)}`;
            }
        }

        return app.currentRecipientUrl;
    }

    async function encryptSelectedFile(form, input) {
        if (!input.files || input.files.length === 0) {
            return true;
        }

        const status = form.querySelector('[data-e2e-status]');
        setStatus(status, 'Dang ma hoa file tren trinh duyet...', 'info');

        const recipientsResponse = await fetch(recipientsUrlForInput(input));
        if (!recipientsResponse.ok) {
            throw new Error('Khong lay duoc danh sach nguoi nhan E2E.');
        }

        const recipientsPayload = await recipientsResponse.json();
        const recipients = recipientsPayload.recipients || [];
        if (recipients.length === 0) {
            if (input.dataset.e2eOptional === 'true') {
                input.value = '';
                setStatus(status, 'Chua co nguoi nhan co public key nen bo qua file dinh kem; form van duoc gui.', 'info');
                return true;
            }
            throw new Error('Chua co public key nguoi nhan. Hay de cac tai khoan lien quan dang nhap it nhat mot lan.');
        }

        const file = input.files[0];
        const aesKey = await cryptoApi.generateKey({ name: 'AES-GCM', length: 256 }, true, ['encrypt', 'decrypt']);
        const rawAesKey = await cryptoApi.exportKey('raw', aesKey);
        const iv = crypto.getRandomValues(new Uint8Array(12));
        const ciphertext = await cryptoApi.encrypt({ name: 'AES-GCM', iv }, aesKey, await file.arrayBuffer());

        const wrappedKeys = [];
        for (const recipient of recipients) {
            const publicKey = await importRecipientPublicKey(recipient.public_key);
            const wrapped = await cryptoApi.encrypt({ name: 'RSA-OAEP' }, publicKey, rawAesKey);
            wrappedKeys.push({
                user_id: recipient.id,
                encrypted_key: bytesToBase64(new Uint8Array(wrapped)),
            });
        }

        ensureHidden(form, 'e2e_original_name').value = file.name;
        ensureHidden(form, 'e2e_original_type').value = file.type || 'application/octet-stream';
        ensureHidden(form, 'e2e_original_size').value = String(file.size);
        ensureHidden(form, 'e2e_iv').value = bytesToBase64(iv);
        ensureHidden(form, 'e2e_keys').value = JSON.stringify(wrappedKeys);

        const encryptedFile = new File([new Blob([ciphertext])], file.name, { type: 'application/octet-stream' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(encryptedFile);
        input.files = dataTransfer.files;

        setStatus(status, 'Da ma hoa E2E. Dang gui ciphertext len server...', 'success');
        return true;
    }

    function bindUploadForms() {
        document.querySelectorAll('form').forEach((form) => {
            const input = form.querySelector('input[type="file"][data-e2e-file]');
            if (!input) return;

            form.addEventListener('submit', async (event) => {
                if (form.dataset.e2eReady === '1') {
                    return;
                }

                event.preventDefault();
                try {
                    await publishPublicKey();
                    await encryptSelectedFile(form, input);
                    form.dataset.e2eReady = '1';
                    form.submit();
                } catch (error) {
                    setStatus(form.querySelector('[data-e2e-status]'), error.message, 'error');
                }
            });
        });
    }

    async function decryptDownload(button) {
        const status = document.querySelector('[data-e2e-download-status]');
        try {
            setStatus(status, 'Dang tai ciphertext...', 'info');
            await publishPublicKey();

            const pair = await getKeyPair();
            const wrappedKey = base64ToBytes(button.dataset.encryptedKey);
            const rawAesKey = await cryptoApi.decrypt({ name: 'RSA-OAEP' }, pair.privateKey, wrappedKey);
            const aesKey = await cryptoApi.importKey('raw', rawAesKey, { name: 'AES-GCM' }, false, ['decrypt']);

            const response = await fetch(button.dataset.ciphertextUrl);
            if (!response.ok) {
                throw new Error('Khong tai duoc ciphertext.');
            }

            const ciphertext = await response.arrayBuffer();
            const plaintext = await cryptoApi.decrypt(
                { name: 'AES-GCM', iv: base64ToBytes(button.dataset.iv) },
                aesKey,
                ciphertext
            );

            const url = URL.createObjectURL(new Blob([plaintext], { type: button.dataset.mime || 'application/octet-stream' }));
            const link = document.createElement('a');
            link.href = url;
            link.download = button.dataset.filename || 'download';
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
            setStatus(status, 'Da giai ma va tai file thanh cong.', 'success');
        } catch (error) {
            setStatus(status, `Giai ma that bai: ${error.message}`, 'error');
        }
    }

    function bindDownloads() {
        document.querySelectorAll('[data-e2e-download]').forEach((button) => {
            button.addEventListener('click', () => decryptDownload(button));
        });
    }

    publishPublicKey().catch(() => {});
    bindUploadForms();
    bindDownloads();
})();
