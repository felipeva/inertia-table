import { defineConfig } from 'vite';
import { resolve } from 'path';
import dts from 'vite-plugin-dts';
import path from 'path';

export default defineConfig({
  plugins: [
    dts({
      rollupTypes: true,
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './js'),
    },
  },
  build: {
    lib: {
      entry: resolve(__dirname, 'js/index.ts'),
      formats: ['es'],
      fileName: (format) => `inertiajs-table-react.${format}.js`,
    },
    rollupOptions: {
      external: [/^@lifespikes\//, /^@inertiajs\//, /^react($|\/)/],
    },
  },
});
