import './click-to-copy.scss'

document.addEventListener('DOMContentLoaded', function () {
    let wrappers = document.querySelectorAll('.click-to-copy-wrapper')
    const enableTextCopy = true // Set to false to disable text copy functionality
    const enableHtmlCopy = true // Set to false to disable HTML copy functionality
    const timeout = 2000 // Timeout in milliseconds to show the "Copied" message

    wrappers.forEach(function (wrapper) {
        // Create the content container element
        let contentContainer = document.createElement('div')
        contentContainer.className = 'click-to-copy-content'
        while (wrapper.firstChild) {
            contentContainer.appendChild(wrapper.firstChild)
        }

        // Create the click-to-copy container element
        let clickToCopyContainer = document.createElement('div')
        clickToCopyContainer.className = 'click-to-copy'
        clickToCopyContainer.appendChild(contentContainer)

        // Create a container for the buttons
        let buttonsContainer = document.createElement('div')
        buttonsContainer.className = 'click-to-copy-buttons'

        if (enableTextCopy) {
            let textButton = createCopyButton('text');
            setupCopyListener(textButton, contentContainer, 'text/plain'); // Pass the contentContainer directly
            buttonsContainer.appendChild(textButton);
        }

        if (enableHtmlCopy) {
            let htmlButton = createCopyButton('html');
            setupCopyListener(htmlButton, contentContainer, 'text/html'); // Correctly set up for HTML copying
            buttonsContainer.appendChild(htmlButton);
        }

        // Append the buttons container next to the content container
        clickToCopyContainer.appendChild(buttonsContainer)

        // Replace the wrapper with the new structure
        wrapper.parentNode.replaceChild(clickToCopyContainer, wrapper)
    })

    function createCopyButton (type) {
        let button = document.createElement('button')
        let messageLabel = type === 'text' ? 'Text' : 'HTML'
        let iconClass = type === 'text' ? 'dashicons-admin-page' : 'dashicons-editor-code'
        button.className = type === 'text' ? 'click-to-copy-button' : 'click-to-copy-html-button'
        button.setAttribute('aria-label', `Click to copy ${ type }`)
        button.innerHTML = `
            <span class="dashicons ${ iconClass }"></span>
            <span class="click-to-copy-text">Copy ${ messageLabel }</span>
        `
        return button
    }

    function setupCopyListener(button, contentContainer, type) {
        button.addEventListener('click', function () {
            if (type === 'text/plain') {
                // Fetch the latest plain text content and trim leading whitespace from each line
                let textToCopy = contentContainer.innerText.replace(/^\s+/gm, '');
                // Use the Clipboard API to copy the trimmed text content
                navigator.clipboard.writeText(textToCopy).then(function () {
                    console.log('Text successfully copied to clipboard');
                }).catch(function (err) {
                    console.error('Could not copy text: ', err);
                });
            } else if (type === 'text/html') {
                // Fetch the latest HTML content and trim leading whitespace from each line
                let htmlToCopy = contentContainer.outerHTML.replace(/^\s+/gm, '');
                let data = new Blob([htmlToCopy], { type: 'text/html' });
                navigator.clipboard.write([new ClipboardItem({ [type]: data })]).then(function () {
                    console.log('HTML successfully copied to clipboard');
                }).catch(function (err) {
                    console.error('Could not copy HTML: ', err);
                });
            }

            let textSpan = this.querySelector('.click-to-copy-text');
            let originalText = textSpan.textContent;
            textSpan.textContent = 'Copied!';
            setTimeout(function () {
                textSpan.textContent = originalText;
            }, timeout);
        });
    }

})
