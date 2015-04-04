# library-metar-taf
Automatically exported from code.google.com/p/library-metar-taf

The PHP library for decoding the METAR or TAF message

Sample result in french

```text
The PHP library for decoding the METAR or TAF message

Sample result in french

 LFMT 011100Z 0112/0212 12005KT 9999 BKN045 BKN100 
     TEMPO 0113/0116 8000 SHRA BKN023 BKN080 
     BECMG 0116/0118 32015KT CAVOK 
     TEMPO 0117/0121 32014G24KT
     
         Station : LFMT                                                                            <-- LFMT
           Heure : Buletin effectue le 01 du mois a 12:00 UTC                                      <-- 011100Z 
       Prevision : debutant le 01 a 12:00h et se terminant le 02 a 12:00h                          <-- 0112/0212 
            Vent : Vent du ESE a une vitesse de 9.3 km/h (force 2)                                 <-- 12005KT 
      Visibilite : Une visibilite superieure a 10 km                                               <-- 9999 
          Nuages : Tres nuageux (50%>75%)(4500 m)                                                  <-- BKN045
                   Tres nuageux (50%>75%)(10000 m)                                                 <-- BKN100
 

        Tendance :                                                                                 <-- TEMPO
       Prevision : debutant le 01 a 13:00h et se terminant le 01 a 16:00h                          <-- 0113/0116
      Visibilite : Une visibilite juqu'a 8 km                                                      <-- 8000
           Meteo : Averse Pluie                                                                    <-- SHRA
          Nuages : Tres nuageux (50%>75%)(2300 m)                                                  <-- BKN023 
                   Tres nuageux (50%>75%)(8000 m)                                                  <-- BKN080

        Tendance :                                                                                 <-- BECMG
       Prevision : debutant le 01 a 16:00h et se terminant le 01 a 18:00h                          <-- 0116/0118
            Vent : Vent du NW a une vitesse de 27.8 km/h (force 4)                                 <-- 32015KT
      Visibilite : Une visibilite superieure a 10 km                                               <-- CAVOK

        Tendance :                                                                                 <-- TEMPO
       Prevision : debutant le 01 a 17:00h et se terminant le 01 a 21:00h                          <-- 0117/0121
            Vent : Vent du NW a une vitesse de 25.9 km/h (force 4), raffale de 44.4 km/h (force 6) <-- 32014G24KT  
```
