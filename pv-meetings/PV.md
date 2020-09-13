# ALICEBLOGS

## PV meeting du 30 juillet 2020

### Notes

|CAT 1|CAT 2|CAT 3|CAT 4|CAT 5|
|:---:|:---:|:---:|:---:|:---:|
|CAT 1-1|CAT 2-1|CAT 3-1|CAT 4-1|CAT 5-1|
|CAT 1-2|CAT 2-2|CAT 3-2|CAT 4-2|CAT 5-2|
|CAT 1-3|CAT 2-3|CAT 3-2|CAT 4-3|CAT 5-3|


    CAT1 = 2020,2021, etc...
    CAT2 = Y1, Y5, ATLAS,etc...
    CAT3 = Drawing, Rooms, etc...
    CAT4 = Studios etc...
    CAT5 = Etudiants etc...

    ------
    TOP NAVIGATION BAR : HOME, BLOG
    ------

* Studios will be roles within the WP structure

* The first column that contains years will be auto generated according to the timestamp of posts

* Users will be ask to chose only the category 2 and 3 while creating new posts

* Posts will have automatically studios category according to the role of the user creating the post

| ELEMENT DE RENDU = DRAWING ETC...|

### Structure filtre utilisateur

    .
    ├── 2020
    │   ├── Y1
    │   │   ├── Drawing
    │   │   │   ├── Studio 1
    │   │   │   │   ├── user 1
    │   │   │   │   │   └── post
    │   │   │   │   ├── user 2
    │   │   │   │   └── user 3
    │   │   │   └── Studio 2
    │   │   │       ├── user 4
    │   │   │       ├── user 5
    │   │   │       │   └── post
    │   │   │       └── user 6
    │   │   └── Rooms
    │   │       ├── Studio 1
    │   │       │   ├── user 1
    │   │       │   │   └── post
    │   │       │   ├── user 2
    │   │       │   └── user 3
    │   │       └── Studio 3
    │   │           └── user 7
    │   │               └── post
    │   └── Y5
    └── 2021

### Structure Fonctionnel

    2020 - CAT1 => timestamp ou catégorie
        Y1 - CAT2 => catégorie appartient à 2020
            Drawing => CAT3 - catégorie
                Studio 1 => CAT4 - role
                    Etudiant 1 => CAT5 - appartient au role Studio 1 - user


# REF.

* https://www.atlasofplaces.com/