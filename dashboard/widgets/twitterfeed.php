<?php print "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."js/widget.js\"></script>";?>
<script type="text/javascript">
                new TWTR.Widget({
                  version: 2,
                  type: 'search',
                  rpp: 15,
                  search: '#tracon',
                  interval: 4500,
                  title: '',
                  subject: 'Tracon @ Twitter',
                  width: 'auto',
                  height: 300,
                  theme: {
                    shell: {
                      background: '#880000',
                      color: '#ffffff'
                    },
                    tweets: {
                      background: '#ffffff',
                      color: '#444444',
                      links: '#800000'
                    }
                  },
                  features: {
                    scrollbar: true,
                    loop: false,
                    live: true,
                    hashtags: true,
                    timestamp: true,
                    avatars: true,
                    toptweets: false,
                    behavior: 'default'
                  }

                }).render().start();
            </script>