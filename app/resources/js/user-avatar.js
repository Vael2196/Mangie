export function initials(name = 'User') {
    const value = name
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(part => part.charAt(0).toLocaleUpperCase())
        .join('');

    return value || 'U';
}

export function createUserAvatar(user, sizeClasses = 'h-9 w-9 text-xs') {
    const wrapper = document.createElement('span');
    wrapper.className =
        `relative inline-flex shrink-0 items-center justify-center overflow-hidden `
        + `rounded-full bg-indigo-100 font-bold text-indigo-700 `
        + `dark:bg-indigo-950 dark:text-indigo-300 ${sizeClasses}`;
    wrapper.dataset.userAvatarId = user.id;
    wrapper.dataset.userAvatarName = user.name;

    const image = document.createElement('img');
    image.dataset.userAvatarImage = '';
    image.alt = user.name;
    image.className = 'h-full w-full object-cover';

    const fallback = document.createElement('span');
    fallback.dataset.userAvatarFallback = '';
    fallback.setAttribute('aria-hidden', 'true');
    fallback.textContent = initials(user.name);

    if (user.avatar_url) {
        image.src = user.avatar_url;
        fallback.classList.add('hidden');
    } else {
        image.classList.add('hidden');
    }

    wrapper.append(image, fallback);

    return wrapper;
}

export function updateUserAvatars(user) {
    document
        .querySelectorAll(`[data-user-avatar-id="${Number(user.id)}"]`)
        .forEach(wrapper => {
            const image = wrapper.querySelector('[data-user-avatar-image]');
            const fallback = wrapper.querySelector('[data-user-avatar-fallback]');

            wrapper.dataset.userAvatarName = user.name;

            const participant = wrapper.closest('[data-user-id]');

            if (participant) {
                participant.title = user.name;
            }

            if (image) {
                image.alt = user.name;
            }

            if (fallback) {
                fallback.textContent = initials(user.name);
            }

            if (user.avatar_url) {
                image.src = user.avatar_url;
                image.classList.remove('hidden');
                fallback?.classList.add('hidden');
            } else {
                image?.removeAttribute('src');
                image?.classList.add('hidden');
                fallback?.classList.remove('hidden');
            }
        });
}

window.MangieAvatars = {
    create: createUserAvatar,
    update: updateUserAvatars,
    initials,
};
