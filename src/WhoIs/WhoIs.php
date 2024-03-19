<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\WhoIs;
use Iodev\Whois\Factory;
use Iodev\Whois\Exceptions\ConnectionException;
use Iodev\Whois\Exceptions\ServerMismatchException;
use Iodev\Whois\Exceptions\WhoisException;
/**
 * WebSocket Component which contains all the logic for sending notifications to the client browsers.
 * Class WebSocketComponent
 * @package App\Core\Components
 *
 * TODO: Need to refactor the send methods sendNotifications
 */
class WhoIs {

    public function checkForDomainName( string $domainName, Response $response, WhoisService $whoisService, WhoisRepository $whoisRepository): Response
    {
        try {
            $whois = Factory::get()->createWhois();
            $info = $whois->isDomainAvailable($domainName);
            $responseData = '';
            if ($info) {
                $responseData = 'Hurray!!! Domain is available.' ;
            }
        } catch (ConnectionException $e) {
            print "Disconnect or connection timeout";
        } catch (ServerMismatchException $e) {
            print "TLD server (.com for google.com) not found in current server hosts";
        } catch (WhoisException $e) {
            print "Whois server responded with error '{$e->getMessage()}'";
        }
        return $this->jsonResponse(response: $response, data: $responseData, status: 200);
    }

    public function checkForLoopUpDomain( string $domainName, Response $response, WhoisService $whoisService, WhoisRepository $whoisRepository): Response
    {
        try {
            $whois = Factory::get()->createWhois();
            $info = $whois->lookupDomain($domainName);
            $responseData = '';
            if ($info) {
                $responseData = $info->text ;
            }
        } catch (ConnectionException $e) {
            print "Disconnect or connection timeout";
        } catch (ServerMismatchException $e) {
            print "TLD server (.com for google.com) not found in current server hosts";
        } catch (WhoisException $e) {
            print "Whois server responded with error '{$e->getMessage()}'";
        }
        return $this->jsonResponse(response: $response, data: $responseData, status: 200);
    }

    public function getDomainInfo( string $domainName, Response $response, WhoisService $whoisService, WhoisRepository $whoisRepository): Response
    {
        try {
            $whois = Factory::get()->createWhois();
            $info = $whois->loadDomainInfo($domainName);
            $responseData = null;
            if (!$info) {
                $responseData = 'Sorry!!, Domain name not available' ;
            }
            else
            {
                $responseData = [
                    'Domain created' => date("Y-m-d", $info->creationDate),
                    'Domain expires' => date("Y-m-d", $info->expirationDate),
                    'Domain owner' => $info->owner,
                    'otherData' => $info
                ] ;
            }
        } catch (ConnectionException $e) {
            print "Disconnect or connection timeout";
        } catch (ServerMismatchException $e) {
            print "TLD server (.com for google.com) not found in current server hosts";
        } catch (WhoisException $e) {
            print "Whois server responded with error '{$e->getMessage()}'";
        }
        return $this->jsonResponse(response: $response, data: $responseData, status: 200);
    }

}
