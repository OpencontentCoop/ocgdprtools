# OpenContent GDPR Tools

L'estensione installa un **datatype** per collezionare accettazioni di informative
a livello di oggetti e di infocollector.

Nell'interfaccia di edit dell'attributo di classe viene richiesto 
l'inserimento di un **testo**, di un **link** e di un *testo del link*.
 * Il testo dovrebbe essere qualcosa come: "Ho preso visione del testo dell'informativa 
 e ne acccetto tutte le clausole"
 * Il link deve puntare a una pagina web dove è contenuto il testo dell'informativa
 * Il testo del link serve per esporre il link e dev'essere qualcosa come "Testo dell'informativa"


L'estensione utilizza l'event listeners *request/input* per **intercettare le richieste 
non contestualizzate in un oggetto**: ad esempio il notification/settings o lo shop/userregister

I listeners vanno configurati in *gdprtools.ini* per dire quali url intercettare e quando un bottone viene premuto 

Ad esempio:

```
[RuntimeAcceptance]
UriList[notification_settings]=notification/settings

[RuntimeAcceptance_notification_settings]
Title=Accettazione informativa privacy sulle impostazioni delle notifiche
Text=Ho preso visione e accetto l'informativa sul trattamento dei dati personali
Link=/Informativa-Privacy
LinkText=Testo informativa privacy
ButtonName=Store
```

interviene quando l'utente preme il bottone di name *Store* che punta all'action *notification/settings*
interrompendo l'esecuzione e mostrando una pagina (*/gpdr/acceptance*) configurata secondo i testi immessi nell'ini: 
l'accettazione dell'informativa riprende l'esecuzione del modulo originale 
e registra il timestamp e l'identificativo in ezpreferences

Nell'impostazione ```[RuntimeAcceptance_default]``` sono impostati i valori di default. 
