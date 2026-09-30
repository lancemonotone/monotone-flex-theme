(function () {
    function updateBodyClass(templateValue) {
        var newClassName = 'page-template-' + templateValue.replace(/\.php$/, '').replace(/\./g, '-').replace(/\//g, '-');

        // Update body class
        document.body.classList.forEach(function (className) {
            if (className.startsWith('page-template-')) {
                document.body.classList.remove(className);
            }
        });
        document.body.classList.add(newClassName);
    }

    function addTemplateChangeListener() {
        var templateSelect = document.getElementById('page_template');

        if (templateSelect) {
            // Set initial body class
            updateBodyClass(templateSelect.value);

            // Listen for changes in the template select
            templateSelect.addEventListener('change', function () {
                updateBodyClass(this.value);
            });
        }
    }

    // Make sure the DOM is fully loaded
    document.addEventListener('DOMContentLoaded', addTemplateChangeListener);
})()

