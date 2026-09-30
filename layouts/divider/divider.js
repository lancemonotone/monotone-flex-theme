import './divider.scss'

// A silly example of how to use a layout component's JS file to manipulate the DOM
document.addEventListener('DOMContentLoaded', function () {
    const dividers = document.querySelectorAll('.divider')
    dividers.forEach(divider => {
        const dividerHeight = divider.getAttribute('data-divider-height')
        divider.style.height = `${ dividerHeight }px`
    })
})
