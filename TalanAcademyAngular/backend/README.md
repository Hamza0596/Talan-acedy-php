# TalanAcademy

dev: 

1. composer self-update --1
2. composer install
3. php bin/console fos:js-routing:dump --format=json --target=public/js/fos_js_routes.json
4. php bin/console d:d:c
5. php bin/console doctrine:schema:update --force
6. php bin/console server:run 


*Générer une clé publique et privéee avec une passphrase à reporter dans le .env:
1. mkdir -p config/jwt
2. openssl genrsa -out config/jwt/private.pem -aes256 4096
3. openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

test:
0. php bin/console d:d:d --force --env=test ( Effacer la base de donnée si elle est déja crée )

1. php bin/console d:d:c --env=test
2. php bin/console d:s:u --force --env=test
3. php bin/console d:f:l --group=test --env=test

* Pour lancer toutes les commandes ensemble :
php bin/console d:d:d --force --env=test && php bin/console d:d:c --env=test && php bin/console d:s:u --force --env=test && php bin/console d:f:l --group=test --env=test

* Les Api Rest :
1. @POST("/api/login_check"): : Pour générer le token
2. @GET("/api/apprentice/current_session"):  Pour récupérer le cours de session en cours de user connecté 
3. @GET("/api/apprentice/dashboard"):  Pour récupérer l' accès au cours (nom du cursus, nb modules, nb leçons, progression,score, nb de jokers, nb jours évalués / nb total)
4. @GET("api/apprentice/staticDashAdmin"): Pour récupèrer les statistiques des cursus
5. @GET("api/apprentice/admin_Session_list?start=1&length=5&order[0][name]=&order[0][dir]=&order[1][name]=&order[1][dir]=asc& search[value]= &extraSearch[0][name]=&extraSearch[0][value]= )":Pour récuperer les données de la dataTable : Session et leur order,Date de debut et fin, le nombre des apprentis et celui confirmé ,
   le score (moy ,min et max) ,évaluation , progression session.

6. @GET("api/apprentice/user:Consulter son profile
7. @GET("api/apprentice/corrections"):Pour récupérer les corrections le jour de la correction
8. @POST("api/apprentice/review/{dayId}"): Evaluer la leçon
9. @POST("api/apprentice/corrections"): Soumettre les corrections
10. @POST("api/apprentice/submission/{dayId}"): Soumettre le repo Git
11. @PATCH("api/apprentice/profil"):Editer le profile de l'apprenti 
12. @POST("api/apprentice/profil/image"):Editer l' image de profile de l'apprenti
13. @PATCH("api/apprentice/profil/password"):Changer son mot de passe
14. GET("/api/admin/cursus/statistics): Consulter les statistiques des cursus
15. GET("/api/admin/sessions): Consulter les statistiques de chaque session
16. GET("/api/admin/resources): Consulter les ressources proposées
17. @Post(/api/apprentice/resources/{dayid}) : Ajouter des ressources
18. @Get(/api/apprentice/profil/image) :récuperer son image de profile
19. @Get(/api/apprentice/curriculum/image/{id}) :Pour récupérer l'image de cursus
20. @Post (/api/apprentice/resource/recommendation/{resourceId}):ajouter une recommendation de ressource proposée



