/**
 * ACF Flexible Content editor helpers:
 * collapse-all, layout-handle colors, layout-choice thumbnails.
 */

function applyHandleBackgrounds() {
	document.querySelectorAll('.acf-fc-layout-handle').forEach((handle) => {
		const layoutType = handle.querySelector('.acf-layout-type')
		if (!layoutType) {
			return
		}
		const style = layoutType.getAttribute('style')
		if (style) {
			handle.setAttribute('style', style)
		}
	})
}

function initCollapseAll() {
	const buttonHtml =
		'<a class="acf-button button button-primary" data-collapse="all" href="#">Collapse All</a>'

	document.querySelectorAll('.acf-field-flexible-content').forEach((field) => {
		const label = field.querySelector('.acf-label')
		if (label && !label.querySelector('[data-collapse="all"]')) {
			label.insertAdjacentHTML('beforeend', buttonHtml)
		}
	})

	const actions = document.querySelectorAll('.acf-flexible-content .acf-actions')
	const lastActions = actions[actions.length - 1]
	if (lastActions && !lastActions.querySelector('[data-collapse="all"]')) {
		lastActions.insertAdjacentHTML('beforeend', buttonHtml)
	}

	document.querySelectorAll('[data-collapse="all"]').forEach((button) => {
		button.addEventListener('click', (event) => {
			event.preventDefault()
			const field = button.closest('.acf-field-flexible-content')
			if (!field) {
				return
			}
			field.querySelectorAll('.acf-flexible-content .layout').forEach((layout) => {
				layout.classList.add('-collapsed')
			})
		})
	})
}

function initLayoutChoiceThumbnails() {
	const config = window.monotoneFlexAdmin
	if (!config || !config.ajaxUrl || !config.nonce) {
		return
	}

	document.querySelectorAll('.tmpl-popup').forEach((template) => {
		const container = document.createElement('div')
		container.innerHTML = template.innerHTML.trim()

		const links = container.querySelectorAll('a[data-layout]')
		const requests = []

		links.forEach((link) => {
			const layout = link.getAttribute('data-layout')
			if (!layout) {
				return
			}

			const data = new FormData()
			data.append('action', config.action)
			data.append('layout', layout)
			data.append('nonce', config.nonce)

			requests.push(
				fetch(config.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: data,
				})
					.then((response) => response.text())
					.then((thumbnailUri) => {
						const url = thumbnailUri.trim()
						if (!url) {
							return
						}
						const img = document.createElement('img')
						img.src = url
						img.alt = ''
						link.prepend(img)
					})
					.catch(() => {
						// Leave the layout choice without a thumbnail.
					})
			)
		})

		Promise.all(requests).then(() => {
			template.innerHTML = container.innerHTML.trim()
		})
	})
}

document.addEventListener('DOMContentLoaded', () => {
	applyHandleBackgrounds()
	initCollapseAll()
	initLayoutChoiceThumbnails()
})
