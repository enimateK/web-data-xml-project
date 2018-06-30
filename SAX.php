<?php

/* Uncomment to see execution time
$timestamp_debut = microtime(true);
*/

include_once('Sax4PHP.php');

class Mondial extends DefaultHandler {

    private $dom;
    private $res;
    private $rootElement;
    private $countriesElement;
    private $seasElement;
    private $coastElement;
    private $countryId;
    private $seaId;
    private $area;
    private $domCountries;
    private $domSeas;
    private $countryList;
    private $coast;


    function startElement($name, $att)
    {
        switch($name) {
            case 'country': ;
                $this->countryId = $att['car_code'];
                $this->area = $att['area'];
                break;
            case 'sea':
                $this->seaId = $att['id'];
                $this->countryList = $att['country'];
                break;
        }
    }

    function endElement($name)
    {
        switch($name) {
            case 'country':
                $this->rootElement['country'][$this->countryId] =
                    [
                        'id' => $this->countryId,
                        'area' => $this->area,
                    ];
                break;
            case 'sea':
                $this->coast = [];
                $countryListArray = explode(" ", $this->countryList);
                foreach($countryListArray as $country) {
                    $this->coast[]['id-p'] = $country;
                }
                $this->rootElement['sea'][$this->seaId] =
                    [
                        "coast" => $this->coast,
                    ];
                break;
        }
    }

    function startDocument()
    {
        $this->dom = new DOMImplementation();
        $this->res = $this->dom->createDocument(
            '',
            'em',
            $this->dom->createDocumentType(
                "em",
                '',
                'em.dtd'
            )
        );
        $this->res->formatOutput = true;
        $this->res->encoding = 'UTF-8';
        $this->domCountries = $this->res->createElement('liste-pays');
        $this->domSeas = $this->res->createElement('liste-espace-maritime');
    }

    function endDocument()
    {
        foreach($this->rootElement['country'] as $country) {
            $this->countriesElement = $this->res->createElement('pays');
            $this->countriesElement->setAttribute("id-p", $country["id"]);
            $this->countriesElement->setAttribute("superficie", $country["area"]);
            $this->domCountries->appendChild($this->countriesElement);
        }
        foreach($this->rootElement['sea'] as $seaId => $sea)
        {
            $this->seasElement = $this->res->createElement('espace-maritime');
            $this->seasElement->setAttribute("id-e", $seaId);
            $this->seasElement->setAttribute("type", "inconnu");

            foreach($sea["coast"] as $coast)
            {
                $this->coastElement = $this->res->createElement('cotoie');
                $this->coastElement->setAttribute("id-p", $coast['id-p']);
                $this->seasElement->appendChild($this->coastElement);
            }
            $this->domSeas->appendChild($this->seasElement);
        }

        $this->res->documentElement->appendChild($this->domCountries);
        $this->res->documentElement->appendChild($this->domSeas);

        $this->res->save('TP_Mondial_SAX.xml');

    }

}

$mondial = file_get_contents('mondial.xml');
try {
    $sax = new SaxParser(new Mondial());
    $sax->parse($mondial);
}
catch(SAXException $e) {
    echo "\n",$e;
}
catch(Exception $e) {
    echo "Capture de l'exception par défaut\n", $e;
}

/* Uncomment to see execution time
$timestamp_fin = microtime(true);
$difference_ms = $timestamp_fin - $timestamp_debut;
echo 'Exécution du script : ' . $difference_ms . ' secondes.';
echo '<!-- Exécution du script : ' . $difference_ms . ' secondes. -->';
*/