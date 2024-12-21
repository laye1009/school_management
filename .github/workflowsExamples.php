
name: CI/CD Pipeline

on:
  push:
    branches:
      - main

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:13
        env:
          POSTGRES_USER: lail
          POSTGRES_PASSWORD: password
          POSTGRES_DB: school_management_test

    env:
      DATABASE_URL: "pgsql://postgres:postgres@127.0.0.1:5432/school_management_test"

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_pgsql

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Verify database connection
        run: pg_isready -h 127.0.0.1 -p 5432 -U postgres

      - name: Set up database
        run: |
          php bin/console doctrine:database:create --if-not-exists
          php bin/console doctrine:migrations:migrate --no-interaction

      - name: Run tests
        run: vendor/bin/phpunit --verbose
        /* */
      - name: Create specific database users
        run: |
            PGPASSWORD=password psql -h 127.0.0.1 -U user -d school_management_test -c "CREATE USER lail_test WITH PASSWORD 'password';"
            PGPASSWORD=password psql -h 127.0.0.1 -U user -d school_management_test -c "GRANT ALL PRIVILEGES ON DATABASE school_management_test TO my_user;"

            - name: Wait for PostgreSQL to be ready
         /* */   
        run: |
          while ! pg_isready -h localhost -p 5432 -U postgres; do
            sleep 1
          done
/** Resoudre le probléme du chargement de DATABASE_URL depuis .env*/
...
steps:
- name: Checkout code
  uses: actions/checkout@v3

- name: Create .env.test file
  run: |
    echo "DATABASE_URL=postgresql://user:password@localhost:5432/school_management_test" > .env.test
    echo "APP_ENV=test" >> .env.test
...
/* ou lancer act en lui passant la variable d'environnement*/
act -e .github/test-event.json -P ubuntu-latest=nektos/act-environments-ubuntu:18.04 \
    -s DATABASE_URL="postgresql://user:password@localhost:5432/school_management_test"

/** ou */
- name: Set database URL environment variable
        env:
          DATABASE_URL: "sqlite:///%kernel.project_dir%/var/school.db"
        run: |
          echo "DATABASE_URL=$DATABASE_URL" >> $GITHUB_ENV