<?php

# Retirer ce fichier en production
# Sert uniquement à vérifier que nginx communique avec php-fpm dans docker-compose.yml

http_response_code(200);
echo "OK";