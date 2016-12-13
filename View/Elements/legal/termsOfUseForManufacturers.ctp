<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
?>
<h1>Nutzungsbedingungen für Hersteller</h1> 

<h2>Betreiber der Plattform</h2>

<p>
    <?php
        echo Configure::read('app.name').'<br />';
        echo implode('<br />', $this->MyHtml->getAddressFromAddressConfiguration());
    ?>
</p>

<p>(im Folgenden kurz: Foodcoop)</p>

<h2>1. Geltung der Nutzungsbedingungen</h2>

<p>1.1. Für alle gegenwärtigen und zukünftigen Leistungen, die die Foodcoop im Rahmen ihrer Internet-Dienstleistung unter der Domain <?php echo Configure::read('app.cakeServerName'); ?> für den Hersteller erbringt (im Folgenden gemeinsam kurz: die Leistung), gelten ausschließlich die nachfolgenden Bedingungen.</p>

<p>1.2. Geschäftsbedingungen des Herstellers kommen nicht zur Anwendung.</p>

<h2>2. Leistungen und Entgelte</h2>

<p>2.1. Die Foodcoop stellt dem Hersteller eine Plattform (FoodCoopShop) zur Verfügung, auf der der Hersteller (nicht exklusiv) Waren und Dienstleistungen zum Verkauf anbietet. Dazu stellt die Foodcoop dem Hersteller ein System (Hersteller-Bereich) zur Verfügung, mit dem der Hersteller die jeweiligen Produkte selbst eintragen kann.</p>

<p>2.2. Der Hersteller verpflichtet sich nur solche Waren und Dienstleistungen zum Verkauf anzubieten, welche in Österreich durch den Hersteller verkauft werden dürfen.</p>

<p>2.3. Der Hersteller nimmt zur Kenntnis, dass auf der Plattform mehrere Hersteller gleichartige oder identische Waren und Dienstleistungen anbieten können.</p>

<p>2.4. Die Auszahlung der Kaufpreise erfolgt am 11. des Folgemonats, sofern die Frist für die Ausübung eines allfälligen Rücktrittsrecht bereits abgelaufen ist.</p>

<p>2.5. Der Vertrag über die Waren und Dienstleistungen kommt ausschließlich zwischen dem Nutzer und dem jeweiligen Hersteller zustande, die Foodcoop vermittelt lediglich den Vertrag.</p>

<p>2.6. Die auf der Website angegebenen Preise verstehen sich inklusive der gesetzlichen Steuer, jedoch exklusive der Verpackungs- und Versandkosten. Allfällige weitere Kosten (etwa Pfand) sind gesondert ausgewiesen.</p> 

<p>2.7. Die Foodcoop hat das Recht, Artikel, die der Hersteller zum Verkauf anbietet, ohne Angabe von Gründen von der Plattform zu nehmen. Der Hersteller hat keinen Rechtsanspruch auf die Veröffentlichung von Waren und Dienstleistungen auf der Plattform.</p>

<?php
    if (Configure::read('app.useManufacturerCompensationPercentage') && $compensationPercentageForTermsOfUse > 0) {
        ?>
		<p>2.8. Für jede über die Plattform verkaufte Ware oder Dienstleistung steht der Foodcoop eine Provision in Höhe von <?php echo $compensationPercentageForTermsOfUse; ?>% des Umsatzes zuzüglich einer allfälligen Umsatzsteuer zu. Die Foodcoop ist berechtigt, diesen Betrag unmittelbar vor der Auszahlung an den Hersteller einzubehalten. Der Hersteller bekommt die Rechnungen der verkauften Produkte (inklusive der einbehaltenen Beträge) automatisch per E-Mail.</p>
        <?php
    }
?>

<h2>3. Schadenersatz und Gewährleistung</h2>

<p>3.1. Die Haftung der Foodcoop und der verwendeten Software (FoodCoopShop) ist ausgeschlossen. Für Schäden infolge schuldhafter Vertragsverletzung haftet die Foodcoop bei eigenem Verschulden oder dem eines Erfüllungsgehilfen nur für Vorsatz oder grobe Fahrlässigkeit. Dies gilt nicht für Schäden an der Person.</p> 

<p>3.2. Der Hersteller verpflichtet sich, die ihn betreffenden Daten vollständig und wahrheitsgemäß auszufüllen und aktuell zu halten.</p>

<p>3.3. Die Foodcoop haftet nicht für Rechtstexte, die die Foodcoop dem Hersteller zur Verfügung stellt. Die Zurverfügungstellung erfolgt unverbindlich.</p>

<p>3.4. Der Hersteller verpflichtet sich, nur solche Inhalte auf die Plattform zu stellen, für die er die für die Veröffentlichung auf der Plattform notwendigen Rechte erworben hat. Der Hersteller haftet daher für die von ihm bereitgestellten Inhalte und wird die Foodcoop schad- und klaglos halten.</p>  

<h2>4. Schlussbestimmungen</h2>

<p>4.1. Erfüllungsort für alle Leistungen aus diesem Vertrag ist der Sitz der Foodcoop.</p>

<p>4.2. Als Gerichtsstand wird das für die Foodcoop sachlich und örtlich zuständige Gericht vereinbart.</p>

<p>4.3. Für Rechtsstreitigkeiten aus diesem Vertrag gilt ausschließlich österreichisches Recht. Die Anwendung des UN-Kaufrechts, der Verweisungsnormen des IPRG und der VO (EG) Nr. 593/2008 des Europäischen Parlaments und des Rates vom 17. Juni 2008 über das auf vertragliche Schuldverhältnisse anzuwendende Recht (Rom I-Verordnung) ist ausgeschlossen.</p> 

<p>4.4. Änderungen oder Ergänzungen dieser Nutzungsbedingungen bedürfen zu ihrer Wirksamkeit der Schriftform.</p>
