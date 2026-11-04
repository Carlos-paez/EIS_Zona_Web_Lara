// Genera los renders SVG del Manual de Usuario.
// Uso: node docs/generate-manual-renders.mjs
import { mkdir, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { scenes1 } from './manual-renders/scenes-1.mjs';
import { scenes2 } from './manual-renders/scenes-2.mjs';
import { scenes3 } from './manual-renders/scenes-3.mjs';
import { scenes4 } from './manual-renders/scenes-4.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const outDir = join(here, '..', 'src', 'Public', 'img', 'manual');

const scenes = [...scenes1, ...scenes2, ...scenes3, ...scenes4];

await mkdir(outDir, { recursive: true });

for (const { name, svg } of scenes) {
    await writeFile(join(outDir, `${name}.svg`), svg, 'utf8');
    console.log(`✓ ${name}.svg`);
}

console.log(`\n${scenes.length} renders generados en ${outDir}`);
