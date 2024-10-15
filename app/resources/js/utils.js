// Get all visible elements with a given class name
export function getVisibleElements(query){
    return Array.from(document.querySelectorAll(query)).filter(s =>
        window.getComputedStyle(s).getPropertyValue('display') != 'none'
    );
}
