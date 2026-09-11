export const initInstallerWizard = () => {
    const wizard = document.querySelector('[data-installer]');

    if (!wizard) {
        return;
    }

    const form = wizard.querySelector('form');
    const sections = Array.from(wizard.querySelectorAll('[data-installer-step]'));
    const progress = Array.from(wizard.querySelectorAll('[data-installer-progress]'));
    const stepLabel = wizard.querySelector('[data-installer-step-label]');
    const backButton = wizard.querySelector('[data-installer-back]');
    const nextButton = wizard.querySelector('[data-installer-next]');
    const finishButton = wizard.querySelector('[data-installer-finish]');
    const databaseType = wizard.querySelector('[name="db_connection"]');
    const databaseServerFields = Array.from(wizard.querySelectorAll('[data-database-server-field]'));
    let step = Number(wizard.dataset.initialStep || 1);

    const render = () => {
        sections.forEach((section) => {
            section.hidden = Number(section.dataset.installerStep) !== step;
        });
        progress.forEach((bar) => {
            bar.classList.toggle('bg-primary', Number(bar.dataset.installerProgress) <= step);
            bar.classList.toggle('bg-base-300', Number(bar.dataset.installerProgress) > step);
        });
        stepLabel.textContent = String(step);
        backButton.hidden = step === 1;
        nextButton.hidden = step === sections.length;
        finishButton.hidden = step !== sections.length;
    };

    const syncDatabaseFields = () => {
        const usesServer = databaseType.value !== 'sqlite';

        databaseServerFields.forEach((container) => {
            container.hidden = !usesServer;
            container.querySelectorAll('input').forEach((input) => {
                input.required = usesServer && input.dataset.optional !== 'true';
            });
        });
    };

    nextButton.addEventListener('click', () => {
        const fields = Array.from(sections[step - 1].querySelectorAll('input, select, textarea'));

        if (fields.every((field) => field.reportValidity())) {
            step = Math.min(sections.length, step + 1);
            render();
            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
    backButton.addEventListener('click', () => {
        step = Math.max(1, step - 1);
        render();
    });
    databaseType.addEventListener('change', syncDatabaseFields);
    form.addEventListener('submit', () => {
        finishButton.disabled = true;
        finishButton.querySelector('[data-installer-finish-label]').hidden = true;
        finishButton.querySelector('[data-installer-spinner]').hidden = false;
    });

    syncDatabaseFields();
    render();
};