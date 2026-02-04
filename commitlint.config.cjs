/**
 * Commitlint configuration for HablaIA monorepo
 *
 * Enforces Conventional Commits format:
 *   type(scope): description
 *
 * Examples:
 *   feat(backend): add pictogram search endpoint
 *   fix(frontend): correct button alignment on mobile
 *   docs: update installation instructions
 */
module.exports = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'type-enum': [
      2,
      'always',
      [
        'feat',
        'fix',
        'docs',
        'test',
        'refactor',
        'chore',
        'style',
        'perf',
        'ci',
        'build',
        'revert',
      ],
    ],
    'scope-case': [2, 'always', 'lower-case'],
    'subject-max-length': [2, 'always', 100],
  },
}
