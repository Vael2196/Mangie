// Average only the outside pixels, so the surround follows the photo's edges.
export function edgeColor(pixels, width, height) {
    let red = 0; let green = 0; let blue = 0; let weight = 0;
    for (let y = 0; y < height; y += 1) {
        for (let x = 0; x < width; x += 1) {
            if (x > 0 && x < width - 1 && y > 0 && y < height - 1) continue;
            const i = (y * width + x) * 4;
            const alpha = pixels[i + 3] / 255;
            red += pixels[i] * alpha; green += pixels[i + 1] * alpha; blue += pixels[i + 2] * alpha;
            weight += alpha;
        }
    }
    return weight ? `rgb(${Math.round(red / weight)}, ${Math.round(green / weight)}, ${Math.round(blue / weight)})` : null;
}

export function tintTaskCover(image) {
    if (!image) return;
    const tint = () => {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 24; canvas.height = 24;
            const context = canvas.getContext('2d', { willReadFrequently: true });
            context.drawImage(image, 0, 0, 24, 24);
            const color = edgeColor(context.getImageData(0, 0, 24, 24).data, 24, 24);
            if (color) image.closest('[data-task-cover-preview]')?.style.setProperty('--task-cover-fill', color);
        } catch { /* Keep the theme fallback when an image cannot be sampled. */ }
    };
    if (image.complete && image.naturalWidth) tint();
    else image.addEventListener('load', tint, { once: true });
}
