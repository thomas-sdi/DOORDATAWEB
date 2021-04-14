update photobucket set image=substring(url, locate(" src=", url)+6, locate('"', url, locate(" src=", url)+6) - locate(" src=",url) - 6) where image="" or image is null;
