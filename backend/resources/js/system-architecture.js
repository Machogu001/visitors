import mermaid from 'mermaid';

const diagramSources = new WeakMap();

const currentTheme = () => {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'neutral';
};

const renderArchitectureDiagrams = async (force = false) => {
    const diagrams = document.querySelectorAll('[data-architecture-diagram]');

    if (diagrams.length === 0) {
        return;
    }

    mermaid.initialize({
        startOnLoad: false,
        securityLevel: 'strict',
        theme: currentTheme(),
        fontFamily: 'inherit',
        flowchart: {
            htmlLabels: false,
            useMaxWidth: true,
            curve: 'basis',
        },
    });

    for (const diagram of diagrams) {
        if (!diagramSources.has(diagram)) {
            diagramSources.set(diagram, diagram.textContent.trim());
        }

        if (force) {
            diagram.removeAttribute('data-processed');
            diagram.textContent = diagramSources.get(diagram);
        }
    }

    await mermaid.run({ nodes: diagrams });
};

let renderedTheme = null;

const renderForCurrentTheme = async () => {
    const theme = currentTheme();
    const force = renderedTheme !== null && renderedTheme !== theme;

    renderedTheme = theme;
    await renderArchitectureDiagrams(force);
};

document.addEventListener('DOMContentLoaded', renderForCurrentTheme);
document.addEventListener('livewire:navigated', renderForCurrentTheme);

const themeObserver = new MutationObserver(() => {
    if (renderedTheme !== currentTheme()) {
        renderForCurrentTheme();
    }
});

themeObserver.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class', 'data-theme'],
});