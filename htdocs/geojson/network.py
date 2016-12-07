#!/usr/bin/env python
"""GeoJSON of a given IEM network code"""
import memcache
import sys
import cgi


def run(network):
    """Generate a GeoJSON dump of the provided network"""
    import json
    import psycopg2.extras
    import datetime

    pgconn = psycopg2.connect(database='postgis', host='iemdb', user='nobody')
    cursor = pgconn.cursor(cursor_factory=psycopg2.extras.DictCursor)

    cursor.execute("""
        SELECT ST_asGeoJson(geom, 4) as geojson, id, name from stations
        WHERE network = %s ORDER by name ASC
    """, (network, ))

    res = {'type': 'FeatureCollection',
           'features': [],
           'generation_time': datetime.datetime.utcnow(
               ).strftime("%Y-%m-%dT%H:%M:%SZ"),
           'count': cursor.rowcount}
    for row in cursor:
        res['features'].append(dict(type="Feature",
                                    id=row['id'],
                                    properties=dict(
                                        sname=row['name'],
                                        sid=row['id']),
                                    geometry=json.loads(row['geojson'])
                                    ))

    return json.dumps(res)


def main():
    sys.stdout.write("Content-type: application/vnd.geo+json\n\n")

    form = cgi.FieldStorage()
    cb = form.getfirst('callback', None)
    network = form.getfirst('network', 'KCCI')

    mckey = "/geojson/network/%s.geojson" % (network,)
    mc = memcache.Client(['iem-memcached:11211'], debug=0)
    res = mc.get(mckey)
    if not res:
        res = run(network)
        mc.set(mckey, res, 3600)

    if cb is None:
        sys.stdout.write(res)
    else:
        sys.stdout.write("%s(%s)" % (cb, res))

if __name__ == '__main__':
    # Go Main Go
    main()
