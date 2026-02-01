/**
 * lint-staged configuration for HablaIA monorepo
 *
 * Handles path transformation for frontend/backend subdirectories
 */
const path = require('path')

module.exports = {
  // Frontend: ESLint with file-specific linting
  'frontend/**/*.{ts,tsx,vue,js}': (absolutePaths) => {
    const cwd = process.cwd()
    const frontendDir = path.join(cwd, 'frontend')

    // Transform absolute paths to relative paths from frontend/
    const relativePaths = absolutePaths.map((file) =>
      path.relative(frontendDir, file)
    )

    // Escape paths for shell and run eslint
    const escapedPaths = relativePaths.map((p) => `"${p}"`).join(' ')
    return `cd frontend && npx eslint --fix ${escapedPaths}`
  },

  // Backend: PHPStan static analysis (requires full context)
  'backend/src/**/*.php': () => {
    // PHPStan needs full project context for accurate analysis
    return 'cd backend && ./vendor/bin/phpstan analyse src --level=8'
  },
}
