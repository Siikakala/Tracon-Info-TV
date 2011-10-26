                <script>
                new TWTR.Widget({
                  version: 2,
                  type: 'search',
                  search: '#tracon',
                  interval: 5000,
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
                    scrollbar: false,
                    loop: false,
                    live: true,
                    hashtags: true,
                    timestamp: true,
                    avatars: true,
                    toptweets: true,
                    behavior: 'default'
                  }
                }).render().start();
                </script>