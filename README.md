# Ninja Forms File Uploads to Azure

## How to use

* Define connection setting (https://site.com/wp/wp-admin/admin.php?page=ninja-forms-uploads&tab=external)
* Switch off 'SAVE TO SERVER' setting on File Upload field.
* Add 'External File Upload' action to form and enable uploading to 'MICROSOFT AZURE'

You can also define constants:

```
define( 'MICROSOFT_AZURE_ACCOUNT_NAME', '' );
define( 'MICROSOFT_AZURE_ACCOUNT_KEY', '' );
define( 'MICROSOFT_AZURE_CONTAINER', '' );
```

The settings have priority over constants.

## Development

Install NodeJS and run `npm install` and `npm run azure` to launch development server.

Use local settings:

- Account Name: `devstoreaccount1`
- Account Key: `Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==`
- Blob Service Endpoint: `http://127.0.0.1:10000/devstoreaccount1`
