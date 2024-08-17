FROM composer as builder
WORKDIR /app
COPY . .
RUN composer install

# Utiliser une image PHP officielle
FROM php:8.1-cli

# Installer tini pour la gestion des signaux
RUN apt-get update && apt-get install -y tini && apt-get clean

# Définir le répertoire de travail
WORKDIR /app

# Copier tous les fichiers du projet dans le conteneur
COPY --from=builder /app /app

# Utiliser tini comme point d'entrée
ENTRYPOINT ["/usr/bin/tini", "--", "php", "/app/bin/aiphpdocs.php"]

# Si vous voulez qu'il exécute une commande par défaut, vous pouvez la spécifier ici
CMD ["--help"]
