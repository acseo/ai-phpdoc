FROM composer as builder
WORKDIR /app
COPY . .
RUN composer install

# Utiliser une image PHP officielle
FROM php:8.1-cli

# Définir le répertoire de travail
WORKDIR /app

# Copier tous les fichiers du projet dans le conteneur
COPY --from=builder /app /app

# Définir le point d'entrée par défaut pour le conteneur
ENTRYPOINT ["php", "/app/bin/aiphpdocs.php"]

# Si vous voulez qu'il exécute une commande par défaut, vous pouvez la spécifier ici
CMD ["--help"]
