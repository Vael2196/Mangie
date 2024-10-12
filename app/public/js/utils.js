// Get all visible elements with a given class name
export function getVisibleElements(className){
    return Array.from(document.querySelectorAll(className)).filter(s =>
        window.getComputedStyle(s).getPropertyValue('display') != 'none'
    );
}
