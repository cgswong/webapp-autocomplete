# Web Application Autocomplete

This is a simple demo PHP web application that showcases a web autocomplete feature using [Google Cloud Datastore](https://cloud.google.com/datastore/). This demonstrates one possible architecture design and the technique, as there are other possibilities such as [Cloud Memorystore](https://cloud.google.com/memorystore/) (currently in beta) and self-hosted Elasticsearch to name a few.

## Feature Roadmap

- [] error handling
- [] logging
- [] Improved fuzzy search, i.e. "CONTAINS" or "IN"
- [] case insensitive searching
- [] popular or context aware search functionality

## How to setup and run the demo

### In Google Cloud Console

1. Create a GCE VM in your desired region with the below settings:

  * Ubuntu 16.04 LTS
  * 1 vCPU and 3.75 GB of memory will suffice along with 20 GB of SSD persistent disk
  * Access to Cloud Datastore API (for demo purposes you can turn on "Allow full access to all Cloud APIs", but this is not recommended for production, see item 2 in the "Prerequisites" for Getting started with the [Google Cloud Datastore API](https://cloud.google.com/datastore/docs/datastore-api-tutorial))
  * Allow HTTP and HTTPS traffic
  * use SSH key if you have one

2. Create a project on GCP Cloud Console

  * Download service account credentials JSON file for your project from https://console.developers.google.com, and save it somewhere on the created VM. You might need to SCP it into your VM, or just copy-paste the contents into a new file on the VM.
  * Create an App Engine instance and deploy it - does not matter what's in it, you need this app enabled to access Datastore APIs (see item 3 in the "Prerequisites" for Getting started with the [Google Cloud Datastore API](https://cloud.google.com/datastore/docs/datastore-api-tutorial))

3. With the above steps successfully completed, we are ready to setup the VM.

### On the GCE VM

1. Log in to the VM

2. Update the OS package manager and install our required software (Apache HTTP Server, PHP, and Memcached):

  ```bash
  sudo apt-get -y update
  sudo apt-get -y install \
    apache2 \
    memcached \
    php \
    php-memcached
  ```

  **Note**: We are installing Memcached and PHP Memcached extension so that we can use Memcached for RAM-based caching. A roadmap item is to use [Google Cloud Memorystore](https://cloud.google.com/memorystore/) as a shared cache to support a distributed (i.e. scale-out) web application.

3. Test if Apache is working, browse to the external IP address of the VM from a browser, `http://{external IP}`.

4. Install git and clone this repo into a directory of your choice:

  ```bash
  sudo apt-get -y install git
  mkdir ~/workspace
  cd ~/workspace
  git clone https://github.com/cgswong/webapp-autocomplete.git
  cd webapp-autocomplete
  ```

5. Relax the permissions on the Apache site folder, `sudo chmod -R 777 /var/www`. **Note that this is a roadmap item to be addressed!**

6. Copy the below files which are required by the web site into the Apache site folder:

  - calldatastore.php
  - form.html
  - loaddatastore.php
  - loaddatastore.sh
  - products.json

  ```bash
  sudo cp \
    calldatastore.php \
    form.html \
    loaddatastore.php \
    loaddatastore.sh \
    products.json \
  /var/www/html
  ```

7. Test access again, browse to the external IP address of the VM from a browser, `http://{external IP}/form.html`. You should see the rudimentary front end of the demo PHP application. At this point if you type something in the text box the output area will show error as it will try to access the Cloud Datastore, which has not yet been configured.

8. Install [composer](https://getcomposer.org/), a dependency manager for PHP, which we will use to install other PHP libraries and dependencies.

  ```bash
  cd /var/www/html
  sudo curl -sS https://getcomposer.org/installer | php
  ```

9. Install some required GCP libraries:

  ```bash
  # Install auth library needed to authenticate against the GCP
  php composer.phar require google/auth
  # Install general GCP library needed for Cloud Datastore access
  php composer.phar require google/cloud
  ```

  **Note**: There should be a vendor directory created under `/var/www/html` with an `autoload.php` file which provides [PHP autoloading](http://php.net/manual/en/language.oop5.autoload.php) functionality.

10. Change the PHP code, line `$projectId = 'development-206303';`, in both `loaddatastore.php` and `calldatastore.php` to point at *your* project. You can find your project ID in your Google Cloud Console.

### Load test data into Cloud Datastore

1. Obviously, you have to do this once. I have used the publicly available BestBuy dataset [here](https://github.com/BestBuyAPIs/open-data-set). In fact, the only file I have used from this dataset is `products.json`, and I have included that file in my repository, so you have it copied inside your `/var/www/html` folder right now, if you have followed the above steps.

2. You will have to run this command to load the data into Cloud Datastore, the script and the file are already in `/var/www/html` by now, from that directory just run the command, `./loaddatastore.sh products.json`. Before you attempt this, here are some things to note:

  * Ensure the shell script is executable, set `chmod +x <filename>` on it if needed.
  * There are almost 52K records in `products.json`, so this command will take around 5 hours to complete due to how loading is done, i.e. a commit per line/object in the file.
  * The shell script that loads the data, i.e. the PHP file that it calls, is **poorly optimized** (quite frankly it is embarrassingly horrible). It connects to Cloud Datastore, and does row based upserts in batches into Cloud Datastore. I have filtered the "SKU" and the "Product Name" fields for the purposes of this demo. The intent is to only demo auto-complete on the "Product" name.
  * Ensure this command is run in a manner where a connection break will not affect the loading, and take a break as it will take a while.

### Demo auto-complete!

After loading is complete, go back to the `form.html` on the browser, start typing something in the text box, and see what happens! If everything was successful, it should auto-complete.

### Caching is working!

You might notice that the first time you type in a letter, it takes a little while to show the results. But if you type the same sequence of letters again, the results show up a lot quicker. This is because we are using Memcache to cache the results for every unique letter-sequence in the code.

Still feeling a bit sluggish? Try adjusting the delay timer which controls the delay following a keypress that a lookup is done. This is done to avoid a lookup after each keypress as it is not only costly (GDS costs), but not that necessary.

### Clean-up

Do not forget to stop the VM, and/or remove the GAE App, and clean up Cloud Datastore. This is a metered platform, treat it like your own electricity bill, even if you are using an account with credits!

## Design Notes

- GQL queries used against Cloud Datastore are case-sensitive. That is why `strtolower(...)` in the file `loaddatastore.php` is used, so all records are stored in Cloud Datastore in lowercase. Before sending the query to GDS, `strtolower`, is also used on the sent input. This allows us to do case insensitive searches, though it does affect the returned results since they will all be in lower case.

- Cloud Datastore query does not appear to support fuzzy search such as "CONTAINS" or "IN" comparisons, so typing something like "top" will **not** find a product with "top" in the name such as "laptop", only something that starts with "top" such as "topin". To get around this, we use `strtolower` on both the uploaded data, and search input data. The better way around this is use to store a lowercased version of the entity along with the original, which is used in our search, while the original (i.e. mixed case) entity is returned for autocomplete.

- Using a local Memcache for this type of use case is okay. That is, consistency is not an overriding concern. However, availability may be an issue in which case using a centralized, distributed Memcache or Redis cluster such as Cloud Memorystore (beta) is a better option. However, given the response times from GDS is within 1 second, the additional complexity may not be worth the investment.

- To implement context-aware, or a popular product search functionality, integration with something such as Cloud Dataflow, or Cloud ML Engine would be needed to learn (a basic rank would work) from what product is actually selected based on what was being typed.
