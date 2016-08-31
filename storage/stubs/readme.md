[![Codeship Status for tightenco/collect](https://codeship.com/projects/7a88b780-04ee-0134-0d48-3e31f9e0f6b8/status?branch=master)](https://codeship.com/projects/154325)

![](https://raw.githubusercontent.com/tightenco/collect/master/collect-logo.png)

# Collect - Illuminate Collections

Import [Laravel's Collections](https://laravel.com/docs/collections) into non-Laravel packages easily, without needing to require the entire `Illuminate\Support` package. ([Why not pull `Illuminate\Support` in framework-agnostic packages](https://yuloh.github.io/2016/dont-use-illuminate-support/))

Written by Taylor Otwell as a part of Laravel's [Illuminate/Support](https://github.com/illuminate/support) package, Collect is just the code from Support needed in order to use Collections on their own.

Lovingly split by Matt Stauffer for [Tighten Co.](http://tighten.co/), with a kick in the butt to finally do it from [@assertchris](https://github.com/assertchris).

## FAQ
 - **Will this develop independently from Illuminate's Collections?**  
    No. Right now it's split manually, but the goal is for it shortly to be split automatically to keep it in sync with Laravel's Collections, even mirroring the release numbers.
 - **Why isn't this just under the Illuminate namespace?**  
    Because that would require adding a lot of complexity and duplication to Illuminate\Support's internal structure. One day Taylor may choose to do that, but right now he hasn't and doesn't have any immediate plans to do so. If he does, we'll deprecate this package and point to the core version.
 - **Why not just use an array?**  
    What a great question. [Tightenite Adam Wathan has a book about that.](http://adamwathan.me/refactoring-to-collections/)