const postcss = require('postcss');
const tailwindcss = require('@tailwindcss/postcss');
const autoprefixer = require('autoprefixer');
const fs = require('fs');
const path = require('path');

const inputFile = './resources/css/app.css';
const outputFile = './public/css/tailwind/app.css';

// Ensure output directory exists
const outputDir = path.dirname(outputFile);
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

// Read the input CSS
const css = fs.readFileSync(inputFile, 'utf8');

// Process with PostCSS
postcss([
  tailwindcss,
  autoprefixer
])
  .process(css, { from: inputFile, to: outputFile })
  .then(result => {
    fs.writeFileSync(outputFile, result.css);
    console.log('✓ Tailwind CSS compiled successfully!');
    console.log(`  Input: ${inputFile}`);
    console.log(`  Output: ${outputFile}`);
    console.log(`  Size: ${(result.css.length / 1024).toFixed(2)} KB`);
  })
  .catch(error => {
    console.error('✗ Error compiling Tailwind CSS:');
    console.error(error);
    process.exit(1);
  });
