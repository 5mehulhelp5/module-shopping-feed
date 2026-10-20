# FTP, SFTP, and gzip uploads

Upload destinations run after a successful feed generation. Establish and validate the feed locally before enabling a transfer.

> Documentation baseline: release 1.2.3 (`v1.2.3`); earlier acceptance is identified by version. Last reviewed: 2026-10-13.

The [UI Component editor](Admin-UI-Component-Forms) masks credentials before provider serialization. After a failed save, new or changed passwords must be re-entered; the previously saved password remains masked. Removing the final upload row and saving clears the destination list. The separate runtime follow-up verifies FTP/SFTP plain and gzip delivery to private Docker test servers on Magento Open Source 2.4.8 and 2.4.9, including credentials, payload checksums, safe failures, and temporary-file cleanup. See the repository's `docs/reviews/2026-10-02-docker-operations-acceptance.md`. External recipient acceptance remains separate.

## Security boundary

Prefer SFTP. FTP sends credentials and data without transport encryption and should only be used when the recipient requires it and the network risk is accepted.

**Unreleased standards branch:** SFTP now requires a trusted server host-key fingerprint for each destination. The module checks that key before sending the username or password. Existing SFTP destinations without a fingerprint fail closed until it is entered. Run `setup:upgrade` to add the fingerprint column. FTP remains available as **FTP (unencrypted)**; an unknown transfer mode is rejected.

The generated local feed remains publicly downloadable under `pub/media/mageos-shopping-feed`, including after a successful upload. SFTP protects the transfer; it does not make the local file private. See [General configuration](General-Configuration).

Use a dedicated remote account restricted to the intended directory. Start with a non-serving or quarantine destination so a test cannot replace an accepted production feed.

## Add a destination

Open the feed, select **Uploads**, then add a row:

| Field | Purpose |
| --- | --- |
| Mode | SFTP or FTP |
| Host | Hostname only, without a URL scheme |
| SFTP Host Key Fingerprint | Trusted `SHA256:...` fingerprint supplied by the recipient's server administrator; required for SFTP |
| Port | Remote service port |
| Username | Dedicated remote user |
| Password | Remote password |
| Path | Remote directory entered after connection |
| Gzip | Compress the generated file before this upload |

The uploaded remote filename is the basename of the local file. **Path** selects the remote directory, not a replacement filename.

Passwords are encrypted with Magento's encryption service before database storage. The Admin shows `******` for a saved password. Leave that placeholder unchanged to retain the existing secret; enter a new value only when rotating it.

The standards branch also keeps stored passwords encrypted in loaded and saved model data. It decrypts a password when opening the transport connection. Cloned destinations retain the encrypted password and pinned host key.

## Confirm the SFTP server key

Ask the recipient's server administrator for the SHA256 fingerprint of the SSH host key used by the SFTP endpoint. Confirm it through an existing trusted support channel. An administrator with access to that server can use `ssh-keygen -lf /etc/ssh/ssh_host_ed25519_key.pub -E sha256` for an Ed25519 key; use the public key file for the algorithm offered by that endpoint.

Enter only the `SHA256:...` value in **SFTP Host Key Fingerprint**. A scanned key alone does not establish trust. If the recipient rotates its host key, uploads stop until you confirm and save the replacement fingerprint. The module follows phpseclib's [host key verification guidance](https://phpseclib.com/docs/3.0/ssh2/connect#host-key-verification).

**Credential handling in 1.1:** masked saves explicitly retain the loaded ciphertext, repeated saves avoid double encryption, and the password column uses `text` to hold encryption overhead. Installation requires the normal `setup:upgrade` schema step. If a previously stored password cannot be decrypted, enter it again; the module does not infer a secret from damaged or plaintext data.

Saving the feed does not prove that the remote connection works. A connection and directory change occur during generation and upload.

When a nonempty **Path** is configured, directory selection must succeed before validation or upload can continue. A missing or inaccessible directory causes an error and no write is attempted in the login directory. Verify the exact path and permissions before retrying.

## Gzip behavior

When **Gzip** is enabled, the module streams the feed into a temporary `.gz` file, uploads that file, and removes the temporary compressed artifact afterward. The normal local feed remains available in its configured format.

## Validate a destination

1. Generate the feed without uploads and record its checksum and size.
2. Add one quarantine destination.
3. Generate the feed manually.
4. Confirm the log records a successful upload to the expected host and path.
5. Download the remote object through an independent client.
6. If gzip is enabled, decompress it and compare its content with the local feed.
7. Confirm the recipient can read the file before moving to a serving path.

Upload failures are logged as warnings. Review [Logs and troubleshooting](Logs-and-Troubleshooting) before retrying repeatedly.
