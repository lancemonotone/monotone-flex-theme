/**
 * This script is used to clean up the 'build-admin' directory after running 'npm run build-admin'.
 * It removes all files and directories except for 'css/admin.css' and 'css/admin.css.map'.
 */

import { readdir, unlink, rmdir, stat } from 'fs/promises';
import { join, dirname, normalize } from 'path';

const dirPath = './build-admin/';

const config = {
    exclude: [
        'css/admin.css',
        'js/admin.js',
    ]
};

// Function to get unique directories from the exclude paths
function getUniqueDirectories(paths) {
    const directories = new Set();
    paths.forEach(path => {
        let currentPath = '';
        dirname(path).split('/').forEach(part => {
            currentPath = join(currentPath, part);
            directories.add(currentPath);
        });
    });
    // // console.log('Excluded directories:', directories);
    return directories;
}

const excludedDirectories = getUniqueDirectories(config.exclude);

async function deleteDirectory(dir) {
    const files = await readdir(dir);
    // console.log(`Reading directory: ${dir}`);

    for (const file of files) {
        const filePath = normalize(join(dir, file));
        // console.log(`Processing filePath: ${filePath}`);

        const fileStat = await stat(filePath);

        // Adjust the relative path calculation
        const normalizedDirPath = normalize(dirPath);
        let relativePath = filePath.substring(normalizedDirPath.length);
        // Replace backslashes with forward slashes
        relativePath = relativePath.replace(/\\/g, '/');
        // console.log(`Processing dirPath.length: ${normalizedDirPath.length}`);
        // console.log(`Processing relativePath: ${relativePath}`);

        if (fileStat.isDirectory()) {
            await deleteDirectory(filePath);

            if (!excludedDirectories.has(relativePath)) {
                const dirContents = await readdir(filePath);
                // console.log(`Directory contents of ${filePath}:`, dirContents);

                if (dirContents.length === 0) {
                    // console.log(`Deleting directory: ${filePath}`);
                    await rmdir(filePath);
                }
            } else {
                // console.log(`Directory excluded from deletion: ${filePath}`);
            }
        } else {
            if (!config.exclude.includes(relativePath)) {
                // console.log(`Deleting file: ${filePath}`);
                await unlink(filePath);
            } else {
                // console.log(`File excluded from deletion: ${filePath}`);
            }
        }
    }
}

deleteDirectory(dirPath).catch(console.error);
