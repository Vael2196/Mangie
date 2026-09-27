import { jsonRequest } from './http';

document.addEventListener('submit', event => {
    const form = event.target.closest('[data-start-sprint-form]');

    if (!form) {
        return;
    }

    event.preventDefault();

    if (form.dataset.submitting === '1') {
        return;
    }

    const submit = form.querySelector('[data-start-sprint-submit]');
    const submitText = form.querySelector('[data-start-sprint-submit-text]');
    const error = form.querySelector('[data-start-sprint-error]');
    const fields = new FormData(form);

    error.textContent = '';
    error.classList.add('hidden');
    form.dataset.submitting = '1';
    submit.disabled = true;
    submitText.textContent = 'Starting...';

    void jsonRequest(form.action, {
        method: 'POST',
        body: {
            board_id: Number(fields.get('board_id')),
            sprint_goal: String(fields.get('sprint_goal') ?? '').trim(),
            end_date: fields.get('end_date'),
        },
    })
        .then(() => window.location.reload())
        .catch(requestError => {
            error.textContent = requestError.message;
            error.classList.remove('hidden');
            delete form.dataset.submitting;
            submit.disabled = false;
            submitText.textContent = 'Start sprint';
        });
});
