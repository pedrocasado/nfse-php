<?php

namespace NFSePHP;

/**
 * Certificate class for handling PFX certificates.
 * Extracts certificate and private key from PFX content.
 */
class Certificate
{
    private ?string $certPem = null;
    private ?string $privateKeyPem = null;

    /**
     * @param string $pfxContent  PFX certificate content (binary string)
     * @param string $pfxPassword Password for the PFX file
     *
     * @throws \RuntimeException if PFX content cannot be parsed
     */
    public function __construct(
        private readonly string $pfxContent,
        private readonly string $pfxPassword,
    ) {
        // Validate PFX content is not empty
        if (empty($this->pfxContent)) {
            throw new \RuntimeException('PFX certificate content cannot be empty');
        }
    }

    /**
     * Get the private key in PEM format.
     *
     * @return string Private key PEM content
     *
     * @throws \RuntimeException if key extraction fails
     */
    public function getKey(): string
    {
        if (null !== $this->privateKeyPem) {
            return $this->privateKeyPem;
        }

        $certs = $this->extractFromPfx();

        // Export private key to PEM format
        if (!openssl_pkey_export($certs['pkey'], $this->privateKeyPem)) {
            throw new \RuntimeException('Failed to export private key: '.openssl_error_string());
        }

        return $this->privateKeyPem;
    }

    /**
     * Get the certificate in PEM format.
     *
     * @return string Certificate PEM content
     *
     * @throws \RuntimeException if certificate extraction fails
     */
    public function getPem(): string
    {
        if (null !== $this->certPem) {
            return $this->certPem;
        }

        $certs = $this->extractFromPfx();

        // Export certificate to PEM format
        if (!openssl_x509_export($certs['cert'], $this->certPem)) {
            throw new \RuntimeException('Failed to export certificate: '.openssl_error_string());
        }

        return $this->certPem;
    }

    /**
     * Get the raw PFX content.
     *
     * @return string PFX content
     */
    public function getPfxContent(): string
    {
        return $this->pfxContent;
    }

    /**
     * Get the PFX password.
     */
    public function getPfxPassword(): string
    {
        return $this->pfxPassword;
    }

    /**
     * Get PEM file paths for HTTP client usage.
     * Creates temporary PEM files from PFX content and returns their paths.
     * Files are cached and reused if they exist and are less than 1 hour old.
     *
     * @return array ['cert' => string, 'key' => string] File paths to certificate and private key PEM files
     *
     * @throws \RuntimeException if file creation fails
     */
    public function getPemFilePaths(): array
    {
        // Create temporary PEM file path based on certificate content hash
        $contentHash = md5($this->pfxContent);
        $tempPemCertPath = sys_get_temp_dir().'/dps_client_cert_'.$contentHash.'.pem';
        $tempPrivateKeyPath = sys_get_temp_dir().'/dps_client_cert_'.$contentHash.'.key';

        // If the temporary PEM file already exists and is recent (less than 1 hour old), reuse it
        if (file_exists($tempPemCertPath) && file_exists($tempPrivateKeyPath) && (time() - filemtime($tempPemCertPath)) < 3600) {
            return [
                'cert' => $tempPemCertPath,
                'key' => $tempPrivateKeyPath,
            ];
        }

        // Get PEM content
        $certPem = $this->getPem();
        $privateKeyPem = $this->getKey();

        // Write to temporary file
        if (false === file_put_contents($tempPemCertPath, $certPem)) {
            throw new \RuntimeException("Failed to write temporary PEM file: {$tempPemCertPath}");
        }

        if (false === file_put_contents($tempPrivateKeyPath, $privateKeyPem)) {
            throw new \RuntimeException("Failed to write temporary PEM file: {$tempPrivateKeyPath}");
        }

        // Set restrictive permissions (readable only by owner)
        chmod($tempPemCertPath, 0o600);
        chmod($tempPrivateKeyPath, 0o600);

        return [
            'cert' => $tempPemCertPath,
            'key' => $tempPrivateKeyPath,
        ];
    }

    /**
     * Extract certificate and private key from PFX content.
     *
     * @return array Array with 'cert' and 'pkey' keys
     *
     * @throws \RuntimeException if PFX parsing fails
     */
    private function extractFromPfx(): array
    {
        // Extract certificate and private key from PFX content
        $certs = [];
        if (!openssl_pkcs12_read($this->pfxContent, $certs, $this->pfxPassword)) {
            $error = openssl_error_string();
            throw new \RuntimeException("Failed to parse PFX content: {$error}");
        }

        return $certs;
    }
}
