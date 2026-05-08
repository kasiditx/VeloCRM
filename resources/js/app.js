import './bootstrap';

window.VeloConfirm = {
    open(message, onConfirm) {
        window.dispatchEvent(new CustomEvent('velo-confirm:open', {
            detail: {
                message,
                onConfirm,
            },
        }));
    },
};

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-velo-confirm]');

    if (!trigger || trigger.dataset.veloConfirmApproved === 'true') {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    window.VeloConfirm.open(trigger.dataset.veloConfirm, () => {
        trigger.dataset.veloConfirmApproved = 'true';
        trigger.click();

        setTimeout(() => {
            delete trigger.dataset.veloConfirmApproved;
        });
    });
}, true);
