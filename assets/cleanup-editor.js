/**
 * This script is used to clean up the 'build-editor' directory after running 'npm run build-editor'.
 * It removes all files and directories except for 'css/editor.css' and 'css/editor.css.map'.
 */

import { readdir, unlink, rmdir, stat } from 'fs/promises';
import { join } from 'path';

const dirPath = './build-editor/';

async function deleteDirectory(dir) {
  const files = await readdir(dir);

  for (const file of files) {
    const filePath = join(dir, file);
    const fileStat = await stat(filePath);

    if (fileStat.isDirectory()) {
      await deleteDirectory(filePath);
      const remainingFilesInDir = await readdir(filePath);
      if (remainingFilesInDir.length === 0 ||
          (remainingFilesInDir.length === 1 && remainingFilesInDir[0] === 'editor.css' && filePath === join(dirPath, 'css'))) {
        // Don't remove 'css' directory in 'build-editor'
        if (filePath !== join(dirPath, 'css')) {
          await rmdir(filePath);
        }
      }
    } else if (filePath !== join(dirPath, 'css/editor.css')) {
      await unlink(filePath);
    }
  }
}

deleteDirectory(dirPath).catch(console.error);
