/**
 * ACF Flexible Content editor helpers:
 * layout-handle colors, layout-choice thumbnails.
 * Re-runs on ACF ready/append because flex markup often lands after DOMContentLoaded.
 *
 * @deprecated collapse/expand-all — ACF Pro includes Expand All / Collapse All
 * (`.acf-fc-expand-all` / `.acf-fc-collapse-all`). `initCollapseAll` kept below
 * but is not called from `bootFlexAdminHelpers`.
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

/**
 * @deprecated Use ACF native Expand All / Collapse All instead.
 * Left in place for reference; not invoked.
 */
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
		if (button.dataset.monotoneBound === '1') {
			return
		}
		button.dataset.monotoneBound = '1'
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
		if (template.dataset.monotoneThumbs === '1') {
			return
		}

		const container = document.createElement('div')
		container.innerHTML = template.innerHTML.trim()

		const links = container.querySelectorAll('a[data-layout]')
		if (!links.length) {
			return
		}

		const requests = []

		links.forEach((link) => {
			if (link.querySelector('img')) {
				return
			}

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
			template.dataset.monotoneThumbs = '1'
		})
	})
}

function bootFlexAdminHelpers() {
	applyHandleBackgrounds()
	// initCollapseAll() — deprecated; ACF ships expand/collapse-all natively.
	initLayoutChoiceThumbnails()
}

// Keep deprecated collapse helper reachable so builds do not tree-shake it away.
window.monotoneFlexDeprecated = {
	initCollapseAll,
}

document.addEventListener('DOMContentLoaded', () => {
	bootFlexAdminHelpers()

	if (typeof window.acf === 'undefined' || typeof window.acf.addAction !== 'function') {
		return
	}

	window.acf.addAction('ready', bootFlexAdminHelpers)
	window.acf.addAction('append', bootFlexAdminHelpers)
	window.acf.addAction('show_field/type=flexible_content', bootFlexAdminHelpers)
})
