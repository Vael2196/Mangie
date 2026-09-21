function csrfToken() {
    return document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');
}

export async function jsonRequest(url, options = {}) {
    const headers = new Headers(options.headers ?? {});

    headers.set('Accept', 'application/json');
    headers.set('X-CSRF-TOKEN', csrfToken() ?? '');

    const socketId = window.Echo?.socketId?.();

    if (socketId) {
        headers.set('X-Socket-ID', socketId);
    }

    let body = options.body;

    if (body !== undefined && !(body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
        body = JSON.stringify(body);
    }

    const response = await fetch(url, {
        ...options,
        headers,
        body,
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.success === false) {
        const firstError = Object.values(data.errors ?? {})[0];
        const error = new Error(
            data.message
                ?? (Array.isArray(firstError) ? firstError[0] : null)
                ?? `Request failed with status ${response.status}.`
        );

        error.status = response.status;
        error.data = data;

        throw error;
    }

    return data;
}
