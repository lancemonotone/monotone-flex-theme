document.addEventListener('DOMContentLoaded', function () {
    // get all anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]:not(.glightbox)')

    // loop through anchor links
    for (let i = 0; i < anchorLinks.length; i++) {
        // add event listener to each anchor link
        anchorLinks[i].addEventListener('click', function (e) {
            // get anchor link href
            const href = this.getAttribute('href')

            if (!href.startsWith('#')) {
                return;
            }

            // prevent default behavior
            e.preventDefault()

            // get anchor link target
            const target = document.querySelector(href)

            // get anchor link target offset top
            const targetOffsetTop = target.offsetTop

            // scroll to anchor link target
            window.scrollTo({
                top     : targetOffsetTop,
                behavior: 'smooth',
            })

        })

    }
})
