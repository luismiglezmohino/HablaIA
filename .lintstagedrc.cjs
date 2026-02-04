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
  'backend/src/**/*.php': (absolutePaths) => {
    const commands = []

    // Check for debug functions in staged files
    const debugCheck = absolutePaths
      .map((p) => `"${p}"`)
      .join(' ')
    commands.push(
      `grep -rn --include="*.php" -E "\\b(var_dump|dd|dump|print_r|die)\\s*\\(" ${debugCheck} && echo "\\n❌ Debug functions found in staged files!" && exit 1 || true`
    )

    // PHPStan needs full project context for accurate analysis
    commands.push('cd backend && ./vendor/bin/phpstan analyse src --level=8')

    return commands
  },
}
