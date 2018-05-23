var PNG = require('pngjs').PNG,
    fs = require('fs'),
    match = require('pixelmatch');

const request = JSON.parse(process.argv[2]);

var threshold = request.threshold || undefined;
var includeAA = request.antialias || false;

var img1 = fs.createReadStream(request.image_1).pipe(new PNG()).on('parsed', doneReading);
var img2 = fs.createReadStream(request.image_2).pipe(new PNG()).on('parsed', doneReading);

function doneReading() {
    if (!img1.data || !img2.data) return;

    if (img1.width !== img2.width || img1.height !== img2.height) {
        console.log('Image dimensions do not match: %dx%d vs %dx%d',
            img1.width, img1.height, img2.width, img2.height);
        return;
    }

    var diff = new PNG({width: img1.width, height: img1.height});

    var diffs = match(img1.data, img2.data, diff.data, diff.width, diff.height, {
        threshold: threshold,
        includeAA: includeAA
    });

    if (request.output) {
        diff.pack().pipe(fs.createWriteStream(request.output));
    }

    var output = {
        'pixels': diffs,
        'error_percentage': (Math.round(100 * 100 * diffs / (diff.width * diff.height)) / 100)
    };

    console.log(JSON.stringify(output));
}