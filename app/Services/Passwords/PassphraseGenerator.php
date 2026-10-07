<?php

namespace App\Services\Passwords;

/**
 * Generates memorable passphrases such as "Maple-River-Copper-Lantern-42": a few random
 * capitalized words and a two-digit number, joined by hyphens. Every choice uses the
 * CSPRNG (random_int), so four words from this list give roughly 43 bits of entropy.
 */
class PassphraseGenerator
{
    /**
     * Short, common and inoffensive English words, all lowercase and unique.
     *
     * @var list<string>
     */
    private const WORDS = [
        'acorn', 'actor', 'adapt', 'agent', 'alarm', 'album', 'alert', 'alpha', 'amber', 'angle',
        'ankle', 'apple', 'apron', 'arena', 'armor', 'arrow', 'atlas', 'attic', 'audio', 'autumn',
        'avenue', 'award', 'bacon', 'badge', 'bagel', 'baker', 'balmy', 'bamboo', 'banana', 'banjo',
        'barrel', 'basil', 'basin', 'basket', 'beach', 'beacon', 'beaver', 'bench', 'berry', 'bicycle',
        'birch', 'biscuit', 'blanket', 'blaze', 'blossom', 'bonus', 'border', 'bottle', 'boulder', 'bounty',
        'bracket', 'branch', 'brave', 'bread', 'breeze', 'brick', 'bridge', 'brook', 'brush', 'bubble',
        'bucket', 'buffalo', 'bundle', 'butter', 'button', 'cabin', 'cactus', 'camel', 'camera', 'candle',
        'canoe', 'canyon', 'carbon', 'cargo', 'carpet', 'carrot', 'castle', 'cedar', 'cellar', 'chalk',
        'channel', 'cherry', 'chess', 'chimney', 'cider', 'circle', 'citrus', 'clever', 'cliff', 'clock',
        'cloud', 'clover', 'coast', 'cobalt', 'cocoa', 'coconut', 'comet', 'compass', 'copper', 'coral',
        'cotton', 'cougar', 'crayon', 'cricket', 'crystal', 'cuckoo', 'dahlia', 'daisy', 'dancer', 'dawn',
        'delta', 'denim', 'desert', 'dinner', 'dolphin', 'donkey', 'dragon', 'drift', 'drum', 'dune',
        'eagle', 'earth', 'easel', 'echo', 'eclipse', 'elbow', 'elder', 'ember', 'emerald', 'engine',
        'falcon', 'feather', 'fennel', 'ferry', 'fiddle', 'field', 'fig', 'finch', 'fjord', 'flame',
        'flannel', 'flute', 'forest', 'fossil', 'fountain', 'fox', 'frost', 'galaxy', 'garden', 'garlic',
        'gazelle', 'gecko', 'geyser', 'ginger', 'giraffe', 'glacier', 'globe', 'goose', 'granite', 'grape',
        'gravel', 'guitar', 'hammer', 'harbor', 'harvest', 'hazel', 'helmet', 'heron', 'hickory', 'honey',
        'horizon', 'hornet', 'husky', 'iceberg', 'igloo', 'indigo', 'island', 'ivory', 'jacket', 'jaguar',
        'jasmine', 'jelly', 'jigsaw', 'jungle', 'kayak', 'kernel', 'kettle', 'kiwi', 'koala', 'ladder',
        'lagoon', 'lantern', 'laptop', 'lava', 'lemon', 'leopard', 'lettuce', 'lilac', 'lily', 'linen',
        'lizard', 'llama', 'lobster', 'locket', 'lotus', 'lunar', 'lynx', 'magnet', 'mango', 'maple',
        'marble', 'meadow', 'melody', 'melon', 'meteor', 'mint', 'mirror', 'mitten', 'monsoon', 'moose',
        'mosaic', 'mountain', 'muffin', 'mural', 'mustard', 'napkin', 'nectar', 'needle', 'nest', 'nickel',
        'noodle', 'nutmeg', 'oasis', 'ocean', 'octave', 'olive', 'onion', 'opal', 'orbit', 'orchid',
        'otter', 'oyster', 'paddle', 'palace', 'panda', 'panther', 'paper', 'parade', 'parrot', 'pasta',
        'peach', 'peanut', 'pebble', 'pecan', 'pelican', 'pencil', 'pepper', 'piano', 'pickle', 'pigeon',
        'pillow', 'pilot', 'pine', 'pirate', 'planet', 'plum', 'pocket', 'polar', 'pony', 'poppy',
        'potato', 'prairie', 'prism', 'pumpkin', 'puzzle', 'quail', 'quartz', 'quill', 'rabbit', 'radar',
        'radish', 'rainbow', 'raisin', 'raven', 'reef', 'ribbon', 'river', 'robin', 'rocket', 'rose',
        'ruby', 'saddle', 'saffron', 'salmon', 'sandal', 'sapphire', 'satchel', 'scarf', 'scooter', 'shadow',
        'shell', 'shore', 'sierra', 'silver', 'sketch', 'sled', 'slope', 'snail', 'sparrow', 'spider',
        'spinach', 'sponge', 'spruce', 'squash', 'squirrel', 'stable', 'star', 'statue', 'stone', 'storm',
        'stream', 'sugar', 'summit', 'sunset', 'swan', 'sweater', 'tablet', 'talon', 'tango', 'teapot',
        'temple', 'thistle', 'thunder', 'tiger', 'timber', 'toast', 'tomato', 'topaz', 'torch', 'tortoise',
        'trail', 'tulip', 'tundra', 'turnip', 'turtle', 'tuxedo', 'umbrella', 'valley', 'vanilla', 'velvet',
        'violet', 'violin', 'volcano', 'voyage', 'waffle', 'walnut', 'walrus', 'wander', 'warbler', 'water',
        'wave', 'willow', 'window', 'winter', 'wizard', 'wombat', 'yacht', 'yarn', 'yogurt', 'zebra',
        'zephyr', 'zinnia', 'anchor', 'apricot', 'badger', 'ballad', 'beetle', 'blender', 'bonfire', 'breadth',
        'bramble', 'buckle', 'bugle', 'cabbage', 'candy', 'caramel', 'cashew', 'cello', 'chapel', 'cheetah',
        'chestnut', 'cinder', 'cobra', 'condor', 'cookie', 'corner', 'crane', 'crocus', 'crumb', 'cupcake',
        'cymbal', 'daffodil', 'dimple', 'dove', 'dragonfly', 'drizzle', 'duckling', 'dynamo', 'easter', 'eggplant',
        'elm', 'emu', 'fable', 'fajita', 'fern', 'firefly', 'flamingo', 'flint', 'foam', 'forge',
        'freckle', 'fudge', 'gadget', 'garnet', 'gentle', 'ginkgo', 'glade', 'glimmer', 'goblet', 'gondola',
        'gopher', 'gorge', 'gust', 'habitat', 'halo', 'hamster', 'harmony', 'hatchet', 'hedge', 'hiker',
        'hippo', 'holly', 'hummus', 'hyacinth', 'iguana', 'inlet', 'iris', 'jade', 'jetty', 'jewel',
        'journey', 'juniper', 'kale', 'kelp', 'kitten', 'kingdom', 'knight', 'lark', 'latte', 'laurel',
        'ledge', 'legend', 'lemur', 'lichen', 'lime', 'lobby', 'lodge', 'lumber', 'lyric', 'macaw',
        'mammoth', 'mantle', 'marlin', 'marsh', 'mason', 'meerkat', 'mesa', 'mill', 'minnow', 'mocha',
        'molasses', 'monarch', 'moss', 'mulberry', 'mushroom', 'narwhal', 'nebula', 'nimble', 'nomad', 'nugget',
        'oak', 'oatmeal', 'ocelot', 'orange', 'osprey', 'owl', 'paprika', 'parsley', 'pasture', 'pearl',
        'penguin', 'peony', 'petal', 'pheasant', 'pinecone', 'pistachio', 'plaza', 'plover', 'pond', 'poplar',
        'porcupine', 'pretzel', 'puffin', 'quarry', 'quiet', 'raccoon', 'rapids', 'ravine', 'redwood', 'ripple',
        'rooster', 'rosemary', 'rumble', 'sage', 'salsa', 'sandbar', 'scallop', 'seagull', 'sequoia', 'sesame',
        'shamrock', 'sherbet', 'shrimp', 'silk', 'skylark', 'snowflake', 'sonnet', 'spark', 'spice', 'spindle',
        'sprout', 'starling', 'stork', 'strudel', 'sunflower', 'swallow', 'sycamore', 'tadpole', 'tamarind', 'tapestry',
        'teal', 'terrace', 'thimble', 'thyme', 'toucan', 'trellis', 'trout', 'truffle', 'tugboat', 'twilight',
        'vapor', 'vessel', 'vine', 'waterfall', 'weasel', 'whisker', 'wildcat', 'willowy', 'windmill', 'wren',
        'yodel', 'yonder', 'zigzag', 'zucchini',
    ];

    public function generate(int $wordCount = 4, string $separator = '-'): string
    {
        $words = [];

        for ($i = 0; $i < $wordCount; $i++) {
            $words[] = ucfirst(self::WORDS[random_int(0, count(self::WORDS) - 1)]);
        }

        $words[] = (string) random_int(10, 99);

        return implode($separator, $words);
    }
}
