To install;

add repo to composer.json;

```
{
  "name": "namespace/your-project",
  "description": "Your awesome website!",
  "repositories": [
    {
      "type": "composer",
      "url": "https://wpackagist.org"
    },
    {
      "type": "git",
      "url": "https://github.com/rmpel/contact-form-quick-edit.git"
    }
  ],
  ...
}

```

and require the plugin;

composer require rmpel/contact-form-quick-edit
