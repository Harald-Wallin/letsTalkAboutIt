# Let's Talk About It - Första arkitektoniska utkastet

### Sidor
-HomePage (nog logged in): groupList (sort by latest activity), LogIn
-HomePage (logged in): myGroupsList (eller userGroupsList), groupList, logOut
-LoginPage: loginForm
-CreateUserPage: createUserForm
-GroupPage: groupCard/större variant av den, applyButton + abortButton om användare inte är medlem  i den redan. Tänker mig alltså att "browse groups" är inbyggd i "loggedIn-HomePage"?
-DiscussionPage: discussionCard/större variant av den + commentForm
-CreateGroupPage: createGroupForm. Här kan en användare skapa en grupp
-CreateDiscussionPage: createDiscussionForm. Här kan alla användare som är medlemmar i en grupp skapa en ny diskussion

-OBS. Endast den som skapade gruppen kan ta bort gruppen (från groupPage) samt
ta bort en hel diskussion från den. Endast den som skapade diskussionen kan ta bort inlägg i diskussionen, förutom författaren till det specifika inlägget!!

### "Components" (om man får kalla det så även i .php)
-groupList: visar lista av 'groupCard's
-userGroupList: samling av 'groupCard's som användaren är medlem i.
-groupCard: visar t.ex gruppnamn, beskrivning, namn på diskussion som senast           interagerats med i den gruppen. Innehåller "apply/abort:Button" om användaren inte redan är medlem i gruppen. 
-discussionList: visar lista med 'discussionCard's
-discussionCard: visar t.ex diskussionens rubrik, och början av dess beskrivning + eventuellt början av senaste inlägget i diskussionen.
-commentCard: består av inläggets författares UnserName och commentForm.
-commentForm: form med endast brödtext, post-knapp och eventuell liten sub-rubrik. Författare och tid sätts automatiskt.
-loginForm: Username + Password (eventuellt även email?)
-createUserForm: Username, fName, lName, Email, password + repeatPassword. Gärna med bra validering på samtliga fält.
-applicationCard: visas i applicationList för medlemmar i en grupp när en användare ansöker om att få vara med i gruppen. Består av Användarnamn samt acceptButton/declineButton.
-applicationDetails: visas då en användare klickar på en applicationCard. Ett litet fönster med Username, fName, lName samt email, och samma accept / decline - knappar. 
-applicationList: visar 'applicationCard's
-searchBar: implementeras för samtliga listor (groupList, discussionList)
-createGroupForm: Gruppnamn, Beskrivning
-createDiscussionForm: Rubrik, beskrivning, initiellt postForm.

### Authorisering
-Endast inloggade användare kan skapa grupper
-Endast gruppmedlemmar kan skapa diskussioner i grupper
-Endast gruppmedlemmar kan läsa/skriva saker i diskussionerna tillhörande gruppen
-Endast gruppmedlemmar kan godkänna applications till gruppen
-Hur behörighet verifieras återstår att se, men iaf server-side.



### Det jag EVENTUELLT eller INTE kommer implementera
-EVENTUELLT alla sidans delete-funktioner
-EVENTUELLT alla searchbars
-Specifik "browseGroup"-sida där man kan sortera grupper efter t.ex nyckelord osv.
- "Mina senaste aktiviteter"; lista med grupper/diskussioner/inlägg sorterad efter 
    användares senaste aktivitet.
-Specifierad "apply"-funktion. T.ex ett form där ansökande användare får skriva motivering till varför denne vill vara med i grupp.
-Användarhistorik/merit. Ett historik- eller "branding"-system per användare där t.ex admins eller användare i grupper ser om användaren tidigare har misskött sig eller på andra sätt kan utvärderas om de är lämpliga eller ej att få gå med i grupp.
-Någon slags state eller penalty för användare som nekats gå med i en grupp där de förhindras "spamma" eller återansöka till en grupp (kanske även bannas?) - de kommer i denna version kunna få "ansöka igen" hur många gånger som helst om de blir nekade att gå med i en grupp.
-En "my page" där man ser de kommentarer man skrivit, de diskussioner man är med i osv.
- Markeringar i de grupper/diskussioner man bidragit till i form av inlägg.

## Databas: Tabeller och relationer

### Tabeller

#### Users
-id
-user
