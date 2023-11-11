docker run --rm -ti ^
           -v %~dp0/..:/app ^
           petrknap/php-critical-section:latest ^
           %*
