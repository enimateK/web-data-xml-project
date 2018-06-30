<?php

/* Uncomment to see execution time
$timestamp_debut = microtime(true);
*/

$doc = new DOMDocument();
$doc->load('mondial.xml');

$res = new DOMDocument();
$res->preserveWhiteSpace = false;
$res->formatOutput = true;
$res->loadxml('<?xml version="1.0" encoding="UTF-8"?><em><liste-pays></liste-pays><liste-espace-maritime></liste-espace-maritime></em>');

$nodes = $doc->documentElement->childNodes;

$countryList = [];
$displayedCountries = [];
$seaList = [];

foreach ($nodes as $n) {
    if ($n instanceOf DOMElement) {
        switch ($n->tagName) {
            case 'country':
                $country = $res->createElement('pays');
                $country->setAttribute('id-p', $n->getAttribute('car_code'));
                $country->setAttribute('superficie', $n->getAttribute('area'));
                foreach ($n->childNodes as $countryChild) {
                    if ($countryChild instanceOf DOMElement) {
                        switch ($countryChild->tagName) {
                            case 'name':
                                $country->setAttribute('nom-p', $countryChild->firstChild->wholeText);
                                break;
                            case 'population':
                                $country->setAttribute('nbhab', $countryChild->firstChild->wholeText);
                                break;
                        }
                    }
                }
                $countryList[$n->getAttribute('car_code')] = $country;
                break;
            case 'river':
                $river = $res->createElement('fleuve');
                $river->setAttribute('id-f', $n->getAttribute('id'));
                foreach ($n->childNodes as $riverChild) {
                    if ($riverChild instanceOf DOMElement) {
                        switch ($riverChild->tagName) {
                            case 'name':
                                $river->setAttribute('nom-f', $riverChild->firstChild->wholeText);
                                break;
                            case 'length':
                                $river->setAttribute('longueur', $riverChild->firstChild->wholeText);
                                break;
                            case 'to':
                                $river->setAttribute('se-jette', $riverChild->getAttribute('water'));
                                break;
                            case 'source':
                                $traveledCountries = explode(' ', $riverChild->getAttribute('country'));
                                if (substr($river->getAttribute('se-jette'), 0, 4) === 'sea-') {
                                    $countryList[$traveledCountries[0]]->appendChild($river);
                                    foreach ($traveledCountries as $traveledCountry) {
                                        if (!in_array($traveledCountry, $displayedCountries)) {
                                            $displayedCountries[] = $traveledCountry;
                                        }
                                    }
                                }
                                break;
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
                break;
            case 'sea':
                $sea = $res->createElement('espace-maritime');
                $sea->setAttribute('id-e', $n->getAttribute('id'));
                $countries = explode(' ', $n->getAttribute('country'));
                foreach($countries as $country){
                    if (!in_array($country, $displayedCountries)) {
                        $displayedCountries[] = $country;
                    }
                    $coast = $res->createElement('cotoie');
                    $coast->setAttribute('id-p', $country);
                    $sea->appendChild($coast);
                }
                foreach($n->childNodes as $seaChild){
                    if($seaChild instanceOf DOMElement && $seaChild->tagName == 'name'){
                        $sea->setAttribute('nom-e',$seaChild->firstChild->wholeText);
                    }
                }
                $sea->setAttribute('type', 'inconnu');
                $seaList[$n->getAttribute('id')] = $sea;
                break;
        }
    }
}

$domCountries = $res->firstChild->firstChild;
foreach($countryList as $country){
    if (in_array($country->getAttribute('id-p'), $displayedCountries)) {
        $domCountries->appendChild($country);
    }
}

$domSeas = $res->firstChild->firstChild->nextSibling;
foreach($seaList as $sea){
    $domSeas->appendChild($sea);
}

$res->save('TP_Mondial_DOM_Sans_Xpath.xml');

/* Uncomment to see execution time
$timestamp_fin = microtime(true);
$difference_ms = $timestamp_fin - $timestamp_debut;
echo 'Exécution du script : ' . $difference_ms . ' secondes.';
echo '<!-- Exécution du script : ' . $difference_ms . ' secondes. -->';
*/
