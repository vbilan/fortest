# Magecredit


## Installation
1. Install this repo with modman, composer, the files link sent to you when you purchased your license. 
2. If you're installing with this repo, composer or modman, make sure to copy the contents of your license file in the original download package you received located in `app/code/community/Wf/CustomerBalance/license.txt` to your app/etc/local.xml. Alternatively, you can add app/code/community/Wf/CustomerBalance/license.txt to your `.gitignore` and include it. For your convenience dev sites are detected using the domain and the license checking wont run. To set your license in the XML simply put your license before the closing `</config>` tag like this:
        ```xml
            <default>
                <customer>
                    <wf_customerbalance>
                        <license>YOUR_LICENSE_HERE</license>
                    </wf_customerbalance>
                </customer>
            </default>
        ```
3. Check out https://magecredit.com/welcome.html to see what you should see.


## Setup
There really isn't any kind of setup. It should all work out of the box, however if you're using a custom checkout 
(such as One Step Checkout) please see https://www.magecredit.com/one_step_checkout.html

## Help
If you're seeing this repo you're probably a trusted developer - so just feel free to 
email jay@magecredit.com with any questions.
