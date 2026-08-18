# Sabaaq Next — All-in-One Start Script
# Prerequisites: PHP 8.2+, Composer, Node.js 20+, MySQL

echo "========================================"
echo "  Sabaaq Next — Starting All Services"
echo "========================================"
echo ""

# Start MySQL if not running
echo "Checking MySQL..."
if command -v mysql &> /dev/null; then
    if ! mysqladmin ping --silent 2>/dev/null; then
        echo "  Starting MySQL..."
        sudo service mysql start 2>/dev/null || sudo systemctl start mysql 2>/dev/null || true
        sleep 2
    fi
    echo "  MySQL is running."
else
    echo "  MySQL not found on PATH. Please start MySQL manually or use Docker."
fi

# Start Redis if not running
echo "Checking Redis..."
if command -v redis-cli &> /dev/null; then
    if ! redis-cli ping &> /dev/null; then
        echo "  Starting Redis..."
        sudo service redis-server start 2>/dev/null || sudo systemctl start redis-server 2>/dev/null || true
        sleep 1
    fi
    echo "  Redis is running."
else
    echo "  Redis not found on PATH. Please start Redis manually or use Docker."
fi

# Start Laravel in background
echo ""
echo "Starting Laravel API (port 8000)..."
cd "$HOME/Desktop/SabaaqNext/laravel" 2>/dev/null || cd "/c/Users/shere/Desktop/SabaaqNext/laravel" 2>/dev/null || cd "$(dirname "$0")/laravel"

composer install --quiet 2>/dev/null || true
cp .env.example .env 2>/dev/null
php artisan key:generate --force --quiet 2>/dev/null || true
php artisan migrate --force --quiet 2>/dev/null || true
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!
echo "  Laravel started (PID: $LARAVEL_PID)"

# Start Nuxt in background  
echo ""
echo "Starting Nuxt Frontend (port 3000)..."
cd "$HOME/Desktop/SabaaqNext/nuxt" 2>/dev/null || cd "/c/Users/shere/Desktop/SabaaqNext/nuxt" 2>/dev/null || cd "$(dirname "$0")/nuxt"

npm install --quiet 2>/dev/null || true
npm run dev -- --host 0.0.0.0 --port 3000 &
NUXT_PID=$!
echo "  Nuxt started (PID: $NUXT_PID)"

echo ""
echo "========================================"
echo "  Services Running:"
echo "  Laravel API:  http://localhost:8000"
echo "  Nuxt App:     http://localhost:3000"
echo "========================================"
echo ""
echo "Press Ctrl+C to stop all services."
echo ""

# Wait for background processes
wait
