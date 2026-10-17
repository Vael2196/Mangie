const reducedMotionQuery = window.matchMedia(
    '(prefers-reduced-motion: reduce)'
);

function initialiseLandingTilt() {
    if (
        reducedMotionQuery.matches
        || !window.matchMedia('(hover: hover) and (pointer: fine)').matches
    ) {
        return;
    }

    document.querySelectorAll('[data-landing-tilt]').forEach((stage) => {
        let frame = null;

        const updateTilt = (event) => {
            const bounds = stage.getBoundingClientRect();
            const x = (event.clientX - bounds.left) / bounds.width;
            const y = (event.clientY - bounds.top) / bounds.height;

            if (frame !== null) {
                window.cancelAnimationFrame(frame);
            }

            frame = window.requestAnimationFrame(() => {
                stage.style.setProperty(
                    '--landing-tilt-x',
                    `${((0.5 - y) * 9).toFixed(2)}deg`
                );
                stage.style.setProperty(
                    '--landing-tilt-y',
                    `${((x - 0.5) * 11).toFixed(2)}deg`
                );
                stage.style.setProperty(
                    '--landing-shine-x',
                    `${(x * 100).toFixed(1)}%`
                );
                stage.style.setProperty(
                    '--landing-shine-y',
                    `${(y * 100).toFixed(1)}%`
                );
            });
        };

        const resetTilt = () => {
            if (frame !== null) {
                window.cancelAnimationFrame(frame);
            }

            frame = window.requestAnimationFrame(() => {
                stage.style.removeProperty('--landing-tilt-x');
                stage.style.removeProperty('--landing-tilt-y');
                stage.style.removeProperty('--landing-shine-x');
                stage.style.removeProperty('--landing-shine-y');
            });
        };

        stage.addEventListener('pointermove', updateTilt);
        stage.addEventListener('pointerleave', resetTilt);
        stage.addEventListener('pointercancel', resetTilt);
    });
}

const vertexShaderSource = `
    attribute vec2 a_position;

    void main() {
        gl_Position = vec4(a_position, 0.0, 1.0);
    }
`;

const fragmentShaderSource = `
    precision highp float;

    uniform vec2 u_resolution;
    uniform vec2 u_pointer;
    uniform float u_time;

    float glow(vec2 point, vec2 centre, float spread) {
        vec2 offset = point - centre;
        return exp(-dot(offset, offset) * spread);
    }

    void main() {
        vec2 uv = gl_FragCoord.xy / u_resolution.xy;
        float aspect = u_resolution.x / u_resolution.y;
        vec2 point = (uv - 0.5) * vec2(aspect, 1.0);
        vec2 pointer = (u_pointer - 0.5) * vec2(aspect, 1.0) * 0.24;
        float time = u_time * 0.00016;

        vec2 indigoCentre = vec2(
            -0.34 * aspect + sin(time * 1.2) * 0.16,
            0.18 + cos(time) * 0.12
        );
        vec2 blueCentre = vec2(
            0.34 * aspect + cos(time * 0.85) * 0.18,
            -0.20 + sin(time * 1.15) * 0.12
        );
        vec2 lilacCentre = vec2(
            sin(time * 0.7) * 0.20,
            0.34 + cos(time * 1.35) * 0.09
        );

        float indigoGlow = glow(point, indigoCentre + pointer, 2.2);
        float blueGlow = glow(point, blueCentre - pointer * 0.45, 2.7);
        float lilacGlow = glow(point, lilacCentre + pointer * 0.25, 4.2);
        float wave = 0.5 + 0.5 * sin(
            point.x * 3.2
            + point.y * 2.4
            + time * 2.2
            + sin(point.y * 3.0 - time) * 0.8
        );

        vec3 deepIndigo = vec3(0.263, 0.220, 0.792);
        vec3 indigo = vec3(0.388, 0.400, 0.945);
        vec3 blue = vec3(0.231, 0.510, 0.965);
        vec3 lilac = vec3(0.780, 0.824, 0.996);

        vec3 colour = mix(deepIndigo, indigo, indigoGlow);
        colour = mix(colour, blue, blueGlow * 0.88);
        colour = mix(colour, lilac, lilacGlow * 0.52);
        colour += wave * 0.075;

        float edge = 1.0 - smoothstep(
            0.18,
            1.05,
            length(point * vec2(0.62, 0.92))
        );
        colour *= 0.88 + edge * 0.12;

        gl_FragColor = vec4(colour, 1.0);
    }
`;

function compileShader(gl, type, source) {
    const shader = gl.createShader(type);

    gl.shaderSource(shader, source);
    gl.compileShader(shader);

    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
        gl.deleteShader(shader);
        return null;
    }

    return shader;
}

function initialiseShaderGradient(container) {
    const canvas = container.querySelector('[data-landing-shader-canvas]');

    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    const gl = canvas.getContext('webgl', {
        alpha: false,
        antialias: false,
        depth: false,
        powerPreference: 'low-power',
    });

    if (!gl) {
        return;
    }

    const vertexShader = compileShader(
        gl,
        gl.VERTEX_SHADER,
        vertexShaderSource
    );
    const fragmentShader = compileShader(
        gl,
        gl.FRAGMENT_SHADER,
        fragmentShaderSource
    );

    if (!vertexShader || !fragmentShader) {
        return;
    }

    const program = gl.createProgram();
    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);

    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        gl.deleteProgram(program);
        return;
    }

    gl.useProgram(program);

    const positionBuffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
    gl.bufferData(
        gl.ARRAY_BUFFER,
        new Float32Array([
            -1, -1,
            1, -1,
            -1, 1,
            -1, 1,
            1, -1,
            1, 1,
        ]),
        gl.STATIC_DRAW
    );

    const positionLocation = gl.getAttribLocation(program, 'a_position');
    gl.enableVertexAttribArray(positionLocation);
    gl.vertexAttribPointer(positionLocation, 2, gl.FLOAT, false, 0, 0);

    const resolutionLocation = gl.getUniformLocation(program, 'u_resolution');
    const pointerLocation = gl.getUniformLocation(program, 'u_pointer');
    const timeLocation = gl.getUniformLocation(program, 'u_time');
    const pointer = { x: 0.5, y: 0.5 };
    let frame = null;
    let isVisible = true;

    const resize = () => {
        const pixelRatio = Math.min(window.devicePixelRatio || 1, 1.5);
        const width = Math.max(1, Math.round(canvas.clientWidth * pixelRatio));
        const height = Math.max(1, Math.round(canvas.clientHeight * pixelRatio));

        if (canvas.width !== width || canvas.height !== height) {
            canvas.width = width;
            canvas.height = height;
            gl.viewport(0, 0, width, height);
        }
    };

    const render = (time = 0) => {
        resize();
        gl.uniform2f(resolutionLocation, canvas.width, canvas.height);
        gl.uniform2f(pointerLocation, pointer.x, pointer.y);
        gl.uniform1f(timeLocation, time);
        gl.drawArrays(gl.TRIANGLES, 0, 6);
    };

    const animate = (time) => {
        render(time);

        if (
            isVisible
            && !document.hidden
            && !reducedMotionQuery.matches
        ) {
            frame = window.requestAnimationFrame(animate);
        } else {
            frame = null;
        }
    };

    const start = () => {
        if (frame === null) {
            frame = window.requestAnimationFrame(animate);
        }
    };

    const stop = () => {
        if (frame !== null) {
            window.cancelAnimationFrame(frame);
            frame = null;
        }
    };

    const visibilityObserver = new IntersectionObserver(([entry]) => {
        isVisible = entry.isIntersecting;

        if (isVisible && !document.hidden && !reducedMotionQuery.matches) {
            start();
        } else {
            stop();
            render(0);
        }
    });

    const resizeObserver = new ResizeObserver(() => render(0));

    container.addEventListener('pointermove', (event) => {
        const bounds = container.getBoundingClientRect();
        pointer.x = (event.clientX - bounds.left) / bounds.width;
        pointer.y = 1 - ((event.clientY - bounds.top) / bounds.height);
    });

    container.addEventListener('pointerleave', () => {
        pointer.x = 0.5;
        pointer.y = 0.5;
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stop();
        } else if (isVisible && !reducedMotionQuery.matches) {
            start();
        }
    });

    visibilityObserver.observe(container);
    resizeObserver.observe(container);
    render(0);

    if (!reducedMotionQuery.matches) {
        start();
    }
}

function initialiseLandingEffects() {
    initialiseLandingTilt();

    document
        .querySelectorAll('[data-landing-shader]')
        .forEach(initialiseShaderGradient);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseLandingEffects);
} else {
    initialiseLandingEffects();
}
