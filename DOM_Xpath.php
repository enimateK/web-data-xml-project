<?php

/* Uncomment to see execution time
$timestamp_debut = microtime(true);
*/

$doc = new DOMDocument();
$doc->load('mondial.xml');
$xpath = new DOMXpath($doc);

$res = new DOMDocument();
$res->preserveWhiteSpace = false;
$res->formatOutput = true;
$res->loadxml('<?xml version="1.0" encoding="UTF-8"?><em><liste-pays></liste-pays><liste-espace-maritime></liste-espace-maritime></em>');

$countryList = [];
$displayedCountries = [];
$seaList = [];

foreach($xpath->query('/mondial/country') as $n) {
    $country = $res->createElement('pays');
    $country->setAttribute('id-p', $n->getAttribute('car_code'));
    $country->setAttribute(
        'nom-p',
        $xpath->query('/mondial/country[./@car_code = "'
            .$n->getAttribute('car_code')
            .'"]/name/text()')
            ->item(0)
            ->wholeText
    );
    $country->setAttribute('superficie', $n->getAttribute('area'));
    $country->setAttribute(
        'nbhab',
        $xpath->query('/mondial/country[./@car_code = "'
            .$n->getAttribute('car_code')
            .'"]/population/text()')
            ->item(0)
            ->wholeText
    );
    $countryList[$n->getAttribute('car_code')] = $country;
}

foreach($xpath->query('/mondial/river') as $n) {
    $river = $res->createElement('fleuve');
    $river->setAttribute('id-f', $n->getAttribute('id'));
    $river->setAttribute(
        'nom-f',
        $xpath->query('/mondial/river[./@id = "'
            .$n->getAttribute('id')
            .'"]/name/text()')
            ->item(0)
            ->wholeText
    );
    if (
        $xpath->query(
        '/mondial/river[./@id = "'
        .$n->getAttribute('id')
        .'"]/length/text()')
            ->item(0)
        instanceOf DOMText
    ) {
        $river->setAttribute(
            'longueur',
            $xpath->query(
                '/mondial/river[./@id = "'
                .$n->getAttribute('id')
                . '"]/length/text()')
                ->item(0)
                ->wholeText
        );
    }
    if (
        $xpath->query(
            '/mondial/river[./@id = "'
            .$n->getAttribute('id')
            .'"]/to'
        )
            ->item(0)
        !== null
    ) {
        $river->setAttribute(
            'se-jette',
            $xpath->query('/mondial/river[./@id = "'
                . $n->getAttribute('id')
                . '"]/to')
                ->item(0)
                ->getAttribute('water'));
    }
    $traveledCountries = explode(
        ' ',
        $xpath->query('/mondial/river[./@id = "'
            .$n->getAttribute('id')
            .'"]/source')
            ->item(0
            )->getAttribute('country')
    );
    if(substr( $river->getAttribute('se-jette'), 0, 4 ) === 'sea-'){
        $countryList[$traveledCountries[0]]->appendChild($river);
        foreach ($traveledCountries as $traveledCountry) {
            if (!in_array($traveledCountry, $displayedCountries)) {
                $displayedCountries[] = $traveledCountry;
            }
        }
    }
    $traveledCountries = explode(' ', $n->getAttribute('country'));
    foreach ($traveledCountries as $traveledCountry) {
        $travel = $res->createElement('parcourt');
        $travel->setAttribute('id', $traveledCountry);
        if (sizeof($traveledCountries) !== 1) {
            $travel->setAttribute('distance', 'inconnue');
        } else {
            $travel->setAttribute('distance', $river->getAttribute('longueur'));
            if (!in_array($traveledCountry, $displayedCountries)) {
                $displayedCountries[] = $traveledCountry;
            }
        }
        $river->appendChild($travel);
    }
}
foreach($xpath->query('/mondial/sea') as $n) {
    $sea = $res->createElement('espace-maritime');
    $sea->setAttribute('id-e', $n->getAttribute('id'));
    $sea->setAttribute('type', 'inconnu');
    if (
        $xpath->query(
            '/mondial/sea[./@id-e = "'
            .$n->getAttribute('id')
            .'"]/name/text()'
        )
        instanceof DOMText
    ) {
        $sea->setAttribute('nom-e',
            $xpath->query(
                '/mondial/sea[./@id-e = "'
                .$n->getAttribute('id')
                .'"]/name/text()')
                ->wholeText
        );
    }
    $countries = explode(' ', $n->getAttribute('country'));
    foreach($countries as $country){
        if (!in_array($country, $displayedCountries)) {
            $displayedCountries[] = $country;
        }
        $coast = $res->createElement('cotoie');
        $coast->setAttribute('id-p', $country);
        $sea->appendChild($coast);
    }

    $seaList[$n->getAttribute('id')] = $sea;
}

$domCountries = $res->firstChild->firstChild;
foreach($countryList as $country) {
    if (in_array($country->getAttribute('id-p'), $displayedCountries)) {
        $domCountries->appendChild($country);
    }
}

$domSeas = $res->firstChild->firstChild->nextSibling;
foreach($seaList as $sea){
    $domSeas->appendChild($sea);
}

$res->save('TP_Mondial_DOM_Avec_Xpath.xml');


/* Uncomment to see execution time
$timestamp_fin = microtime(true);
$difference_ms = $timestamp_fin - $timestamp_debut;
echo 'Exécution du script : ' . $difference_ms . ' secondes.';
echo '<!-- Exécution du script : ' . $difference_ms . ' secondes. -->';
*/