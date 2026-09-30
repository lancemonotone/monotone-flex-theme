import './expando.scss'

class ExpandoCard {
    constructor (expandoLimit = 25) {
        this.expandoLimit = expandoLimit
        this.init()
    }

    splitContent (html, expandoLimit) {
        // Replace <p> and </p> tags with newline characters
        let cleanedHtml = html.replace(/<p>/g, '').replace(/<\/p>/g, '<br><br>')

        let words = cleanedHtml.split(/\s+/)
        let firstPart = words.slice(0, expandoLimit).join(' ')
        let secondPart = words.slice(expandoLimit).join(' ')

        return { firstPart, secondPart }
    }

    init () {
        const expandos = document.querySelectorAll('.expando')
        expandos.forEach((expando, index) => {
            const expandText = expando.dataset.expandtext || 'expand'
            const collapseText = expando.dataset.collapsetext || 'collapse'
            const expandoLimit = parseInt(expando.dataset.expandolimit) || this.expandoLimit
            const { firstPart, secondPart } = this.splitContent(expando.innerHTML, expandoLimit)

            if (!secondPart) {
                return
            }

            const hiddenContentId = `hidden-content-${ index }`

            expando.innerHTML = `<p>
        <span class="visible-content">${ firstPart }<span class="ellipsis" style="display: inline;">...</span></span>
        <span id="${ hiddenContentId }" class="hidden-content" style="display: none;">${ secondPart }</span></p>
        <a href="#" role="button" aria-expanded="false" aria-controls="${ hiddenContentId }" class="toggle-link">expand text</a>
      `

            const toggleLink = expando.querySelector('.toggle-link')
            toggleLink.addEventListener('click', (e) => {
                e.preventDefault()
                const hiddenContent = expando.querySelector('.hidden-content')
                const ellipsis = expando.querySelector('.ellipsis')
                const isExpanded = e.target.getAttribute('aria-expanded') === 'true'

                if (!isExpanded) {
                    hiddenContent.style.display = 'inline'
                    ellipsis.style.display = 'none'
                    e.target.textContent = collapseText
                    e.target.setAttribute('aria-expanded', 'true')
                } else {
                    hiddenContent.style.display = 'none'
                    ellipsis.style.display = 'inline'
                    e.target.textContent = expandText
                    e.target.setAttribute('aria-expanded', 'false')
                }
            })

            // Keyboard accessibility
            toggleLink.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault()
                    toggleLink.click()
                }
            })
        });
    }
}

new ExpandoCard();
