<?php
/**
 * ElectroSuite Reseller Security Functions Class.
 *
 * Provides static methods for encrypting and decrypting data,
 * primarily API keys, using Libsodium and WordPress salts.
 *
 * @author      ElectroSuite
 * @category    Security
 * @package     ElectroSuite Reseller/Classes
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'ElectroSuite_Reseller_Security' ) ) {

/**
 * ElectroSuite_Reseller_Security Class.
 */
class ElectroSuite_Reseller_Security {

    /**
     * Check if the Sodium extension is loaded.
     * Logs an error if not available.
     *
     * @return bool True if Sodium is available, false otherwise.
     */
    private static function is_sodium_available() {
        if ( ! extension_loaded('sodium') ) {
            error_log( 'ElectroSuite Reseller Security Error: Sodium PHP extension is required but not loaded. Encryption/Decryption unavailable.' );
            // Consider adding an admin notice here if needed
            return false;
        }
        return true;
    }

    /**
     * Derives a 32-byte encryption key from WordPress salts.
     * Uses HKDF if available (PHP >= 7.1.2 with hash extension), otherwise uses SHA256.
     *
     * @return string|false 32-byte binary key string on success, false on failure (missing salts).
     */
    private static function get_encryption_key() {
        // Use LOGGED_IN_KEY and LOGGED_IN_SALT as recommended for per-installation keys
        if ( ! defined( 'LOGGED_IN_KEY' ) || ! defined( 'LOGGED_IN_SALT' ) || LOGGED_IN_KEY === 'put your unique phrase here' || LOGGED_IN_SALT === 'put your unique phrase here' ) {
             error_log( 'ElectroSuite Reseller Security Error: LOGGED_IN_KEY or LOGGED_IN_SALT are not defined correctly in wp-config.php. Cannot derive encryption key.' );
             return false;
        }

        $key_material = LOGGED_IN_KEY . LOGGED_IN_SALT;

        // Use HKDF for better key derivation if available
        if ( function_exists( 'hash_hkdf' ) ) {
            // SODIUM_CRYPTO_SECRETBOX_KEYBYTES is 32
            return hash_hkdf( 'sha256', $key_material, SODIUM_CRYPTO_SECRETBOX_KEYBYTES, 'electrosuite-reseller-encryption', '' );
        } else {
            // Fallback to simple SHA256 hash (ensure binary output)
            return hash( 'sha256', $key_material, true );
        }
    }

    /**
     * Encrypts data using Libsodium's secret-key authenticated encryption.
     *
     * @param string $data The plaintext data to encrypt.
     * @return string|false Base64 encoded string (nonce + ciphertext) on success, false on failure.
     */
    public static function encrypt( $data ) {
        if ( empty($data) ) {
            return ''; // Don't encrypt empty strings, return empty
        }

        if ( ! self::is_sodium_available() ) {
            return false; // Sodium not available
        }

        $key = self::get_encryption_key();
        if ( false === $key ) {
            return false; // Failed to get key
        }

        try {
            // Generate a nonce: SODIUM_CRYPTO_SECRETBOX_NONCEBYTES is 24
            $nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );

            // Encrypt
            $ciphertext = sodium_crypto_secretbox( (string) $data, $nonce, $key );

            // Combine nonce and ciphertext, then base64 encode
            $encrypted_data = base64_encode( $nonce . $ciphertext );

            // Clear sensitive variables from memory
            sodium_memzero( $key );
            sodium_memzero( $data ); // If $data itself was sensitive and passed by reference potentially
            sodium_memzero( $ciphertext );

            return $encrypted_data;

        } catch ( Exception $e ) {
            error_log( 'ElectroSuite Reseller Security Error during encryption: ' . $e->getMessage() );
            // Ensure key is cleared even on exception if possible
            if (isset($key)) sodium_memzero($key);
            return false;
        }
    }

    /**
     * Decrypts data encrypted with the encrypt() method.
     *
     * @param string $encrypted_data Base64 encoded string (nonce + ciphertext).
     * @return string|false The original plaintext data on success, false on failure (e.g., bad key, tampered data, sodium unavailable).
     */
    public static function decrypt( $encrypted_data ) {
        if ( empty($encrypted_data) ) {
            return ''; // Return empty if trying to decrypt empty
        }

        if ( ! self::is_sodium_available() ) {
            return false; // Sodium not available
        }

        $key = self::get_encryption_key();
        if ( false === $key ) {
            return false; // Failed to get key
        }

        // Decode base64
        $decoded = base64_decode( $encrypted_data, true ); // Use strict mode
        if ( false === $decoded ) {
             error_log( 'ElectroSuite Reseller Security Warning: Failed to base64 decode data during decryption.' );
             sodium_memzero( $key );
             return false;
        }

        // Check minimum length (Nonce length + at least 1 byte ciphertext)
        if ( mb_strlen( $decoded, '8bit' ) < ( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + 1 ) ) {
             error_log( 'ElectroSuite Reseller Security Warning: Encrypted data is too short to be valid.' );
             sodium_memzero( $key );
             return false;
        }

        // Extract nonce and ciphertext
        $nonce = mb_substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit' );
        $ciphertext = mb_substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit' );

        try {
            // Decrypt
            $decrypted = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );

            // Clear sensitive variables from memory
            sodium_memzero( $key );
            sodium_memzero( $decoded );
            sodium_memzero( $ciphertext );
            // Don't clear $encrypted_data as it was input param
            // Don't clear $decrypted as it's the return value

            if ( false === $decrypted ) {
                // This indicates decryption failure (bad key, tampered data, etc.)
                error_log( 'ElectroSuite Reseller Security Warning: Decryption failed. Data may be corrupted, tampered with, or wp-config.php salts may have changed.' );
                return false;
            }

            return $decrypted;

        } catch ( Exception $e ) {
            // Exceptions aren't typically thrown by sodium_crypto_secretbox_open for decryption failures,
            // but catch just in case of unexpected issues.
            error_log( 'ElectroSuite Reseller Security Error during decryption: ' . $e->getMessage() );
             // Ensure key is cleared even on exception if possible
            if (isset($key)) sodium_memzero($key);
            return false;
        }
    }

} // End class ElectroSuite_Reseller_Security

} // End if class_exists
?>