#!/usr/bin/env bash
#
# sync-to-claude-code.sh
# Convierte agentes de formato OpenCode a formato Claude Code
#
# Estructura esperada:
#   agents/           ← Fuente (formato OpenCode)
#   skills/           ← Fuente (formato compatible ambos)
#   .claude/agents/   ← Destino (formato Claude Code, generado)
#   .claude/skills    → enlace a ../skills
#   .opencode/agents  → enlace a ../agents
#   .opencode/skills  → enlace a ../skills
#
# OpenCode: lee directamente via enlaces simbolicos
# Claude Code: necesita conversion de formato (este script)
#
# Uso: ./sync-to-claude-code.sh
#

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Directorios
AGENTS_SOURCE="$PROJECT_ROOT/agents"
SKILLS_SOURCE="$PROJECT_ROOT/skills"
CLAUDE_AGENTS="$PROJECT_ROOT/.claude/agents"
CLAUDE_SKILLS="$PROJECT_ROOT/.claude/skills"
OPENCODE_DIR="$PROJECT_ROOT/.opencode"
OPENCODE_AGENTS="$OPENCODE_DIR/agents"
OPENCODE_SKILLS="$OPENCODE_DIR/skills"

# Tools para Claude Code (todas disponibles, el agente se autolimita por instrucciones)
CLAUDE_ALL_TOOLS="Read, Write, Edit, Bash, Glob, Grep, WebFetch, WebSearch, Task"

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[OK]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_skip() { echo -e "${YELLOW}[SKIP]${NC} $1"; }
log_section() { echo -e "\n${BLUE}=== $1 ===${NC}"; }

to_kebab_case() {
    echo "$1" | sed 's/_/-/g'
}

# Crear enlace simbolico si no existe
create_symlink() {
    local target="$1"
    local link="$2"
    local name="$3"

    if [[ -L "$link" ]]; then
        log_info "$name (enlace existe)"
    elif [[ -e "$link" ]]; then
        log_warn "$name (existe pero no es enlace)"
    else
        ln -s "$target" "$link"
        log_info "$name (enlace creado)"
    fi
}

process_agent() {
    local source_file="$1"
    local filename
    filename=$(basename "$source_file" .md)
    local agent_name
    agent_name=$(to_kebab_case "$filename")

    # Extraer description del frontmatter
    local description
    description=$(grep -m1 "^description:" "$source_file" | sed 's/^description: *//' || echo "")

    # Extraer contenido (todo despues del segundo ---)
    local content
    content=$(awk '/^---$/{c++;next}c>=2' "$source_file")

    # Generar formato Claude Code
    cat > "$CLAUDE_AGENTS/$agent_name.md" << EOFCLAUDE
---
name: $agent_name
description: $description
tools: $CLAUDE_ALL_TOOLS
---

$content
EOFCLAUDE

    log_info "$filename → $agent_name.md"
}

should_skip_agent() {
    case "$1" in
        orchestrator.md|test_agent.md) return 0 ;;
        *) return 1 ;;
    esac
}

validate_agent() {
    local source_file="$1"
    local filename
    filename=$(basename "$source_file")
    local warnings=0

    # Verificar frontmatter con description
    if ! grep -q "^description:" "$source_file"; then
        log_warn "$filename: falta 'description' en frontmatter"
        ((warnings++)) || true
    fi

    # Verificar frontmatter con mode
    if ! grep -q "^mode:" "$source_file"; then
        log_warn "$filename: falta 'mode' en frontmatter"
        ((warnings++)) || true
    fi

    # Verificar seccion Quality Gates
    if ! grep -q "Quality Gates" "$source_file"; then
        log_warn "$filename: falta seccion 'Quality Gates'"
        ((warnings++)) || true
    fi

    # Verificar seccion Restricciones Fatales
    if ! grep -q "Restricciones Fatales" "$source_file"; then
        log_warn "$filename: falta seccion 'Restricciones Fatales'"
        ((warnings++)) || true
    fi

    return $warnings
}

main() {
    echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║   Sync to Claude Code                            ║${NC}"
    echo -e "${BLUE}║   Convierte OpenCode format → Claude Code format ║${NC}"
    echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"

    # Verificar fuentes
    if [[ ! -d "$AGENTS_SOURCE" ]]; then
        echo "ERROR: No existe $AGENTS_SOURCE"
        exit 1
    fi
    if [[ ! -d "$SKILLS_SOURCE" ]]; then
        echo "ERROR: No existe $SKILLS_SOURCE"
        exit 1
    fi

    # === OPENCODE: Enlaces simbolicos ===
    log_section "OpenCode (enlaces simbolicos)"
    mkdir -p "$OPENCODE_DIR"
    create_symlink "../agents" "$OPENCODE_AGENTS" ".opencode/agents → ../agents"
    create_symlink "../skills" "$OPENCODE_SKILLS" ".opencode/skills → ../skills"

    # === CLAUDE CODE: Conversion de agentes ===
    log_section "Claude Code (conversion de agentes)"
    mkdir -p "$CLAUDE_AGENTS"

    local agent_count=0
    local warn_count=0
    for f in "$AGENTS_SOURCE"/*.md; do
        [[ -f "$f" ]] || continue
        if should_skip_agent "$(basename "$f")"; then
            log_skip "$(basename "$f") (excluido)"
            continue
        fi
        # Validar antes de convertir (warnings, no bloquea)
        if ! validate_agent "$f"; then
            ((warn_count++)) || true
        fi
        process_agent "$f"
        ((agent_count++)) || true
    done

    # === CLAUDE CODE: Enlace para skills ===
    log_section "Claude Code (skills)"
    mkdir -p "$PROJECT_ROOT/.claude"
    create_symlink "../skills" "$CLAUDE_SKILLS" ".claude/skills → ../skills"

    # === RESUMEN ===
    log_section "Resumen"
    echo -e "Agentes convertidos: ${GREEN}$agent_count${NC}"
    if [[ $warn_count -gt 0 ]]; then
        echo -e "Agentes con warnings: ${YELLOW}$warn_count${NC}"
    fi
    echo ""
    echo "Estructura final:"
    echo "  agents/             Fuente (formato OpenCode)"
    echo "  skills/             Fuente (compatible ambos)"
    echo "  .opencode/agents    → ../agents (enlace)"
    echo "  .opencode/skills    → ../skills (enlace)"
    echo "  .claude/agents/     Generado (formato Claude Code)"
    echo "  .claude/skills      → ../skills (enlace)"
}

main "$@"
