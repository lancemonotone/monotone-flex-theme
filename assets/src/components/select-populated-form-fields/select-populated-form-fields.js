document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input').forEach(input => {
        //add event listener for change
        input.addEventListener('change', () => {
            if (input.value) {
                input.classList.add('has-value')
            } else {
                input.classList.remove('has-value')
            }
        })
    })
})
