<div align="center">
<h1>Monotone Flex Theme</h1>
  <img src="logo.jpg" width="400" />
</div>

> Theme-only extract of the WordPress theme formerly shipped inside the `troyweb_wp` full-site assessment repo. Folder name on disk can be anything; Theme Name in `style.css` is **Monotone Flex Theme**.

Monotone is a flexible, modular WordPress theme that leverages the power of Advanced Custom Fields (ACF) for layout configuration. It uses a modern Vite asset build process for SCSS, Bootstrap and JavaScript, and all fonts are served directly from the theme, ensuring a consistent and fast user experience.

## Features

- **ACF Flexible Content Fields**: Monotone uses ACF to provide a block-based layout system. Each block comes with its own configuration settings, allowing for a high degree of customization. All
  field settings are backed up by a JSON file, making it easy to import and export configurations.

- **Vite Build System**: Monotone uses Vite as its build system, providing fast and efficient compilation of assets. It supports both editor and admin style options, compiled from SCSS.

- **Fonts Served from Theme Directory**: To ensure a consistent user experience and faster load times, all fonts are served directly from the theme directory.

- **Custom Branding Options**: Monotone provides custom branding options in the Customizer, allowing you to easily customize the site's logo, colors, and more.

- **Responsive Image Markup**: Monotone handles the image sizes, conversion to WEBP, and responsive image markup.

- **Custom Shortcodes**: Monotone includes custom shortcodes in the MCE editor for easy content creation.

- **Bootstrap Menu Walker**: Monotone uses the WP Bootstrap Navwalker to create a Bootstrap-enabled menu.

## Classes

The theme's functionality is organized into classes, each residing in the `classes` directory. Here are the main classes:


- `ACF`: Handles the registration of ACF fields and blocks, as well as the import and export of field settings.
- `ACF Flex Page`: Handles the layout configuration and the rendering of Flexible Content Layout modules.
- `Admin Template Filter`: Filter pages by template in the admin page list.
- `Assets`: Handles the enqueueing of scripts and styles, and the dequeueing of WP Block Library CSS.
- `Autoactivate`: Automatically activates the theme's default plugins.
- `Blog`: Handles the blog functionality and rewrite rules.
- `Branding`: Adds custom branding options to the Customizer.
- `Constants`: Defines the theme's constants.
- `Custom Post Types`: Registers the theme's custom post types.
- `Custom Taxonomies`: Registers the theme's custom taxonomies.
- `Disable Gutenberg`: Disables the Gutenberg editor for specific post types and page templates.
- `Images`: Handles the image sizes, conversion to WEBP, and responsive image markup.
- `Navigation`: Handles the navigation menus.
- `Replace LSEP`: Replaces the line separator character with a break.
- `Shortcodes`: Adds custom shortcodes to the MCE editor.
- `SVG Icons`: Adds a library of SVG icons to the theme.
- `Theme:`: Handles the theme's setup and configuration.
- `Widgets`: Registers the theme's widgets and sidebars.
- `WP Bootstrap Navwalker`: Registers the WP Bootstrap Navwalker for Bootstrap-enabled menus.

## Build Setup

The theme uses npm for package management. To get started, navigate to the `assets` directory and install the dependencies:

```bash
npm install
```

To build the project and watch for changes, use the following command:

```bash
npm run build
```

In addition to the main build, there are two additional builds for the editor and admin styles. These are built with SCSS and are located in the `assets/scss` directory.

```bash
npm run editor
npm run admin
```

You can build all the assets at once (with no watch) by running the following command:

```bash
npm run build-all
```

## Contributing

Contributions are welcome!

## License

Monotone is licensed under the GNU General Public License v2 or later.


