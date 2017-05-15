/**
 * Created by mike on 17.15.4.
 */

window.onload = function() {
    var visitor = document.getElementById('visitor');

    var img = document.getElementById('banner');
    var image_loaded = function(a) {
        var url = "/addVisitor?data="+ visitor.textContent;
        var xhr = new XMLHttpRequest();
        xhr.onload = function() {
            if (this.readyState === 4) {
                if (this.status === 200) {
                    var res = JSON.parse(this.response);
                    var count = document.getElementById('count');
                    if (count) {
                        count.textContent = res.data;
                    }
                } else {
                    console.log('error response', this.response);
                }
            }
        };
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send();
    };
    if (img.complete) {
        image_loaded(1);
    } else {
        image_loaded(2);
    }
};


