<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Sas;

use AzureOss\Storage\Blob\Helpers\DateHelper;
use AzureOss\Storage\Common\ApiVersion;
use AzureOss\Storage\Common\Auth\StorageSharedKeyCredential;
use GuzzleHttp\Psr7\Query;

/**
 * Builds an Azure Storage account shared access signature (SAS).
 */
final class AccountSasBuilder
{
    private string $version;

    private string $services;

    private string $resourceTypes;

    private string $permissions;

    private ?\DateTimeInterface $startsOn = null;

    private \DateTimeInterface $expiresOn;

    private ?SasIpRange $ipRange = null;

    private ?SasProtocol $protocol = null;

    private ?string $encryptionScope = null;

    /** Creates an empty account SAS builder. */
    public static function new(): self
    {
        return new self;
    }

    /** Sets the Storage service version signed by the SAS. */
    public function setVersion(string $version): AccountSasBuilder
    {
        $this->version = $version;

        return $this;
    }

    /** Sets the storage services accessible through the SAS. */
    public function setServices(string|AccountSasServices $services): AccountSasBuilder
    {
        $this->services = (string) $services;

        return $this;
    }

    /** Sets the service, container, and object resource types accessible through the SAS. */
    public function setResourceTypes(string|AccountSasResourceTypes $resourceTypes): AccountSasBuilder
    {
        $this->resourceTypes = (string) $resourceTypes;

        return $this;
    }

    /** Sets the operations permitted by the SAS. */
    public function setPermissions(string|AccountSasPermissions $permissions): AccountSasBuilder
    {
        $this->permissions = (string) $permissions;

        return $this;
    }

    /** Sets the earliest instant at which the SAS is valid. */
    public function setStartsOn(\DateTimeInterface $startsOn): AccountSasBuilder
    {
        $this->startsOn = $startsOn;

        return $this;
    }

    /** Sets the instant at which the SAS expires. */
    public function setExpiresOn(\DateTimeInterface $expiresOn): AccountSasBuilder
    {
        $this->expiresOn = $expiresOn;

        return $this;
    }

    /** Restricts requests to the specified source IP address or range. */
    public function setIpRange(SasIpRange $ipRange): AccountSasBuilder
    {
        $this->ipRange = $ipRange;

        return $this;
    }

    /** Restricts requests to HTTPS, or permits both HTTPS and HTTP. */
    public function setProtocol(SasProtocol $protocol): AccountSasBuilder
    {
        $this->protocol = $protocol;

        return $this;
    }

    /** Sets the encryption scope required for requests authorized by the SAS. */
    public function setEncryptionScope(string $encryptionScope): AccountSasBuilder
    {
        $this->encryptionScope = $encryptionScope;

        return $this;
    }

    /** Signs and returns the account SAS query string without a leading question mark. */
    public function build(StorageSharedKeyCredential $sharedKeyCredential): string
    {
        $signedStart = $this->startsOn !== null ? DateHelper::formatAs8601Zulu($this->startsOn) : null;
        $signedExpiry = DateHelper::formatAs8601Zulu($this->expiresOn);
        $signedIp = $this->ipRange !== null ? (string) $this->ipRange : null;
        $signedProtocol = $this->protocol?->value;
        $signedVersion = $this->version ?? ApiVersion::latestGA()->value;

        $stringToSign = [
            $sharedKeyCredential->accountName,
            $this->permissions,
            $this->services,
            $this->resourceTypes,
            $signedStart,
            $signedExpiry,
            $signedIp,
            $signedProtocol,
            $signedVersion,
            $this->encryptionScope,
        ];
        $stringToSign = array_map(fn ($str) => urldecode($str ?? ''), $stringToSign);
        $stringToSign = implode("\n", $stringToSign)."\n";

        $signature = urlencode($sharedKeyCredential->computeHMACSHA256($stringToSign));

        return Query::build(array_filter([
            'sv' => $signedVersion,
            'ss' => $this->services,
            'srt' => $this->resourceTypes,
            'sp' => $this->permissions,
            'st' => $signedStart,
            'se' => $signedExpiry,
            'sip' => $signedIp,
            'spr' => $signedProtocol,
            'ses' => $this->encryptionScope,
            'sig' => $signature,
        ], fn (?string $value) => $value !== null), false);
    }
}
