#!/usr/bin/env bash

# setdown.sh - Stop and remove Docker containers for the Cars & Inspections API
# Usage: ./setdown.sh

echo "🛑 Stopping Cars & Inspections API..."
echo ""

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "❌ Docker Compose is not installed."
    exit 1
fi

# Stop and remove containers
echo "🗑️  Removing containers..."
docker compose down

echo ""
echo "✅ Services stopped and removed!"
echo ""
echo "💡 To start again, run: ./setup.sh"
