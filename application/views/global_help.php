<h2>Ohjeet</h2>
<div id="help-accord">
	<h3><a href="#" class="head-links">Scroller</a></h3>
	<div>
		<strong>HUOM!</strong>
		<ul>
			<li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan.</li>
	    	<li>Tyhjiä rivejä ei huomioida tallennuksessa.</li>
		    <li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.</li>
		</ul>
	</div>
	<h3><a href="#" class="head-links">Rulla</a></h3>
	<div>
		<strong>HUOM!</strong>
		<ul>
			<li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan.</li>
			<li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.</li>
			<li>Diat näkyvät noin sekunnin pidempään kuin määrität tässä.</li>
  		</ul>
	</div>
	<h3><a href="#" class="head-links">Diat</a></h3>
	<div>
		<p>
			Voit käyttää [salinnimi-nyt] , [salinnimi-next] ja [aika] -tageja tekstin seassa.
		</p>
		<ul>
			<li><strong>nyt</strong> mitä tällä hetkellä salissa tapahtuu (- jos ei mitään)</li>
			<li><strong>next</strong> mitä tapahtuu seuraavaksi, kellonaikoineen (esim. 15 - 18 Cosplay-kisat (WCS ja pukukisa))</li>
			<li><strong>aika</strong> tuottaa tämänhetkisen tunnin (esim 10 - 11).</li>
		</ul>
		<p>
			Esim. [iso_sali-nyt] voisi tuottaa lauantaina klo 10:45 tekstin "Avajaiset". Next osaa katsoa päivämäärärajojen yli, joten
			jos ohjelmatietokannassa on ohjelmanumero vaikkapa tiistaille, se tulostaa vaikkapa (ti) 14 - 15 Tapahtuman jälkeinen ohjelmanumero.
		</p>
		<p>
			Muista tarkistaa tuotoksesi! Tallenna save-napista, lisää dia rullaan instanssin infotest ainoaksi diaksi ja tsekkaa
			viereisestä välilehdestä tulokset (jossa pitäisi olla kyseinen instanssi pyörimässä). Dian voi poistaa painamalla Delete slide.
		</p>
	</div>
	<h3><a href="#" class="head-links">Streamit</a></h3>
	<div>
		<p>
			Täältä voit esikatsella streameja, ennenkuin pistät sen frontendiin. Erittäin suositeltavaa.
		</p>
	</div>
	<h3><a href="#" class="head-links">Frontendit</a></h3>
	<div>
		<p>
    		Tällä sivulla voit tarvittaessa pakottaa jonkin frontendin näyttämään vaikkapa pelkkää diashowta, esim. infossa.<br/><br/>
    		<strong>HUOM!</strong>
            <ul>
                <li>Listauksessa on vain 15min sisään itsestään ilmoittaneet frontendit</li>
                <li>Globaalia asetusta käyttävien näyttöasetukset eivät vaikuta mihinkään.</li>
                <li>Frontendit, jotka eivät ole ilmoittaneet itsestään yli viiteen minuuttiin, asetetaan käyttämään globaalia asetusta.</li>
                <li>Frontendit, jotka eivät ole ilmoittaneet itsestään yli viikkoon, poistetaan automaattisesti</li>
            </ul>
		</p>
	</div>
	<h3><a href="#" class="head-links">Lokikirja</a></h3>
	<div>
		<p>
		<strong>Pikanäppäimet:</strong>
		 <ul>
		     <li><strong>F2</strong>: Valitse hakukenttä.</li>
		     <li><strong>F6</strong>: Valitse tyypiksi "Tiedote" ja valitse viestikenttä</li>
		     <li><strong>F7</strong>: Valitse tyypiksi "Ongelma" ja valitse viestikenttä</li>
		     <li><strong>F8</strong>: Valitse tyypiksi "Kysely" ja valitse viestikenttä</li>
		     <li><strong>F9</strong>: Valitse tyypiksi "Löytötavara" ja valitse viestikenttä</li>
		     <li><strong>F10</strong>: Valitse tyypiksi "Muu" ja valitse viestikenttä</li>
		 </ul>
		</p>
		<p>
		Lokikirjassa voit kuitata rivin klikkaamalla riviä. Tekstit yliviivataan ja rivi
		himmennetään tällöin. Voit perua kuittauksen klikkaamalla riviä uudelleen vastaamalla
		"Poista" varmistuskyselyyn. Viemällä hiiren hetkeksi kuitatun rivin päälle näet
		kuittausajan ja kuittaajan.<br/>
		Voit poistaa rivin klikkaamalla riviä hiiren kakkosnäppäimellä, ja valitsemalla poista.
		Poistosta tulee vielä varmistuskysely, ennenkö rivi poistetaan.<br/>
		Voit muokata riviä klikkaamalla riviä hiiren kakkosnäppäimellä ja valitsemalla muokkkaa.
		Muuta riviä tarvittavin osin ja paina muokkaa. Saat ilmoituksen "Rivi muokattu" jos muokkaus
		onnistui ja pikkuikkuna sulkeutuu automaattisesti. Mikäli editointi ei onnistunut, saat ilmoituksen
		tästä eikä pikkuikkuna sulkeudu.
		</p>
		<p>
		Lokikirjan haussa voit käyttää niinkutsuttuja regexpejä. Tämä tarkoittaa, että
		mikäli haluat hakea esimerkiksi kaikki löytötavara tai tiedote -tyyppiset rivit
		kirjoita kenttään löytö|tiedote. Putkimerkki on siis looginen TAI-haku. Mikäli
		haluat löytää kaikki henkilön bob kirjoittamat kyselyt, kirjoita bob kysely.
		Välilyönti on siis looginen JA-haku. Voit myös yhdistellä näitä. Esimerkiksi,
		jos haluat löytää kaikki tiedotteet ja löytötavat, jotka bob tai igor on lisännyt,
		kirjoita tiedote|löytö bob|igor. Haku hakee myös päivämääriä ja kellonaikoja.
		</p>
	</div>
	<h3><a href="#" class="head-links">Tuotantosuunnitelma</a></h3>
	<div>
		<p>
		Tuotantosuunnitelmassa voit muokata riviä klikkaamalla sitä hiiren kakkosnäppäimellä
		ja valitsemalla muokkaa. Samasta valikosta voit myös poistaa rivin.
		Muokkausdialogissa päivitä-näppäimen klikkaus ei sulje dialogia, eli sitä voi käyttää
		myös välitallennukseen.
		</p>
	</div>
	<h3><a href="#" class="head-links">Tekstiviestit</a></h3>
	<div>
		<p>
			Työkalulla voi lähettää yksittäisiä viestejä, saman viestin monelle (ryhmäviesti)
			tai massapostituksen CSV-lähetyksenä. Näille kaikille on yhteistä se, että ne haluavat
			puhelinnumeron <strong>kansainvälisessä</strong> muodossa! Numerossa ei saa olla myöskään 
			mitään välimerkkejä, kuten välilyöntejä tai viivoja. Ainoastaan alussa oleva plus-merkki on sallittu.
			Viestit eivät lähde (=numero ei osu regexpiin ja ei tule lisätyksi kantaan) muuten.
		</p>
		<p>
			Höylä lähettää viestinsä numerosta +3584573950776
		</p>
		<p>
			Kahdessa alemmassa alasvetokentässä on 15 viimeisintä lähetettyä ja vastaanotettua tekstiviestiä. Kentistä
			löytyy myös napit, joilla voit tarkastella kaikkia lähetettyjä tai vastaanotettuja tekstiviestejä. 
			Napit avaavat erillisen laatikon, jonka sisältö päivitetään aina sitä avattaessa, eli tiedot voi päivittää
			sulkemalla laatikon ok-napista ja avaamalla sen uudelleen.
		</p>
		<p>
			Mikäli jono ei tunnu lyhenevän, taustalähetysprosessi lienee kuollut. Se yritetään automaattisesti käynnistää
			uudelleen minuutin kuluessa, mutta mikäli näin ei tapahtu, ilmoita tekniikkavastaavalle.
		</p>
		<?php if($page == "tekstarit"): ?>
		<p>
			<button onclick="$('#dialog-tekstari-help').dialog('open');">Avaa virhekoodiluettelo</button>
		</p>
		<?php endif; ?>

	</div>
</div>
<br/>
<h4 style="padding-left:10px;">Kaikkialla toimivat pikanäppäimet</h4>
<p>
 <ul>
     <li><strong>F4</strong>: Valitse chat-laatikon kirjoituskenttä.</li>
 </ul>
</p>