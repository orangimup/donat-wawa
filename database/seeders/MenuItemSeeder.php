<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // ===== CLASSICS =====
            [
                'image' => 'nNf7kHKxBxfMAUQPbtwntqjIEHq3sCQ7wElWAkR1.png',
                'name' => 'Original Glaze', 'category' => 'classics', 'badge' => 'best_seller',
                'price' => 3000, 'stock' => 45, 'status' => 'active',
                'description' => 'Donat klasik yang selalu jadi andalan di etalase kami. Adonannya diuleni lembut sehingga tekstur dalamnya empuk dan ringan di mulut. Lapisan glaze tipis berwarna keemasan membungkus seluruh permukaannya dengan kilau yang menggoda. Rasa manisnya pas, tidak berlebihan, dan tidak membuat eneg. Cocok dinikmati sendirian bersama kopi atau teh hangat di pagi hari. Kalau baru pertama kali mencoba, mulailah dari yang satu ini.',
            ],
            [
                'image' => 'AwqFz4S4SbuQ1S2Wsuz91740TXbTkSGv64MUtFO6.png',
                'name' => 'Vanilla Bean', 'category' => 'classics', 'badge' => null,
                'price' => 3000, 'stock' => 25, 'status' => 'active',
                'description' => 'Donat lembut dengan glaze vanila berwarna krem yang halus dan mengilap. Aroma vanilanya langsung terasa begitu kamu membuka boksnya. Manisnya lembut dan bersih, jadi rasa adonannya tetap terasa. Teksturnya empuk dengan sedikit kenyal di bagian pinggir. Pilihan aman untuk semua usia, termasuk anak-anak. Enak dimakan hangat maupun setelah dingin.',
            ],
            [
                'image' => 'DljJPi0eCZYzTV3amCfZXYJpr3s79Pjyb9CMqkrf.png',
                'name' => 'Choco Melt', 'category' => 'classics', 'badge' => 'best_seller',
                'price' => 3000, 'stock' => 38, 'status' => 'active',
                'description' => 'Donat empuk yang diselimuti cokelat pekat berkilau dan dihiasi garis white chocolate yang rapi. Cokelatnya lumer di mulut dengan rasa yang kaya dan sedikit pahit di ujung. Kontras dengan garis putihnya yang manis, rasanya jadi seimbang. Adonan di dalamnya tetap lembut dan ringan, tidak seret. Setiap gigitan terasa seperti hadiah kecil untuk pecinta cokelat. Salah satu yang paling cepat habis di etalase kami.',
            ],
            [
                'image' => 'fV4F0ZnRdOnc72l7bT4tg6YpeQ7BPCZ7WfastCuF.png',
                'name' => 'Strawberry Dream', 'category' => 'classics', 'badge' => null,
                'price' => 3000, 'stock' => 30, 'status' => 'active',
                'description' => 'Glaze stroberi berwarna pink cerah menutup seluruh permukaan donat ini. Di atasnya ada garis white icing dan taburan sprinkles warna-warni yang bikin senyum. Rasa stroberinya manis dengan sedikit sentuhan asam yang segar. Adonannya empuk dan lembut seperti donat klasik kami. Tampilannya ceria, jadi cocok untuk ulang tahun atau acara kumpul. Anak-anak biasanya langsung jatuh cinta pada pandangan pertama.',
            ],
            [
                'image' => 'j5krkWLPIoLMVzpY8GYwVrW2oTuLQ1WUF2wmxPuz.png',
                'name' => 'Blueberry Burst', 'category' => 'classics', 'badge' => null,
                'price' => 4000, 'stock' => 20, 'status' => 'active',
                'description' => 'Glaze blueberry berwarna ungu tua yang mengilap menutupi seluruh bagian atas donat. Rasanya manis dengan sentuhan asam segar khas buah beri. Warnanya yang pekat terlihat cantik dan menggugah selera. Adonan di bawahnya lembut dan sedikit kenyal. Perpaduannya ringan, jadi tidak terasa terlalu manis. Cocok untuk kamu yang suka rasa buah di dalam donat.',
            ],

            // ===== SPECIAL =====
            [
                'image' => '9S3X9w3YvbyGU8LzMk3pg5zxieWoftSeWFVmmxLy.png',
                'name' => 'Golden Cheese', 'category' => 'special', 'badge' => null,
                'price' => 5000, 'stock' => 16, 'status' => 'active',
                'description' => 'Donat lembut yang dilapisi krim keju halus lalu ditutup parutan keju melimpah. Perpaduan gurih dan manisnya bikin ketagihan sejak gigitan pertama. Parutan kejunya memberi tekstur lembut yang meleleh di mulut. Adonan donatnya empuk sehingga tidak kalah dengan topping di atasnya. Rasa kejunya terasa jelas tanpa membuat eneg. Favorit mereka yang tidak terlalu suka donat manis.',
            ],
            [
                'image' => '0aerQTGEBiTIeKAkoJSrZVxJT0QZzxKWW0xDGasY.png',
                'name' => 'Taro Candy Pop', 'category' => 'special', 'badge' => 'new',
                'price' => 4000, 'stock' => 22, 'status' => 'active',
                'description' => 'Glaze taro berwarna ungu lembut yang creamy dengan aroma khas umbi taro. Di atasnya ada zigzag white chocolate dan sprinkles warna-warni yang ceria. Sepotong permen semangka mungil jadi hiasan yang bikin donat ini langsung mencuri perhatian. Rasanya manis, gurih lembut, dan tidak terlalu berat. Adonan donatnya empuk sehingga seimbang dengan topping yang meriah. Cantik untuk difoto dan enak untuk dihabiskan.',
            ],
            [
                'image' => '1XzgHsuoD5pKF7INFSz0WDVNCp1kTf6G423gJ9iV.png',
                'name' => 'Matcha Oreo', 'category' => 'special', 'badge' => 'best_seller',
                'price' => 4000, 'stock' => 27, 'status' => 'active',
                'description' => 'Glaze matcha hijau yang lembut dengan rasa teh hijau yang sedikit pahit dan harum. Tepi donatnya dilapisi remah Oreo yang renyah dan gurih. Perpaduan pahit matcha dan manis cokelat Oreo terasa seimbang. Tekstur lembut di dalam dan renyah di pinggir bikin setiap gigitan menarik. Adonannya empuk dan ringan. Wajib dicoba oleh penggemar matcha.',
            ],
            [
                'image' => 'EM0yYCP0VSY3Cki9oOcfShMUyLMlUR0H74QWRenc.png',
                'name' => 'Matcha Crunch', 'category' => 'special', 'badge' => null,
                'price' => 4000, 'stock' => 18, 'status' => 'active',
                'description' => 'Glaze matcha berwarna hijau zaitun yang pekat dan mengilap menutup bagian atas donat. Zigzag white chocolate menambah rasa manis yang lembut di atasnya. Di pinggirnya ada taburan kacang cincang dan remah biskuit yang renyah. Rasa matcha-nya lebih tegas dan sedikit pahit, dengan aroma yang harum. Kombinasi lembut dan renyah membuatnya terasa kaya tekstur. Cocok dipasangkan dengan kopi hitam atau teh tawar.',
            ],
            [
                'image' => '2l7R4aSmf2Tm4WYR8uUIUllMdd1GMpg5SwVyJEuJ.png',
                'name' => 'Choco Peanut Crunch', 'category' => 'special', 'badge' => null,
                'price' => 4000, 'stock' => 14, 'status' => 'active',
                'description' => 'Donat berlapis cokelat yang ditutup rapat dengan kacang cincang panggang. Kacangnya gurih dan renyah, sehingga setiap gigitan terdengar garing. Cokelat di bawahnya memberi rasa manis yang lembut dan sedikit pahit. Perpaduan gurih dan manisnya klasik dan selalu enak. Adonannya empuk, jadi kontras dengan toppingnya yang renyah. Pilihan tepat untuk yang suka camilan gurih manis.',
            ],
            [
                'image' => '6i5td3ALfhNQr7JRGL9OnfayrlWGYRDJbRCGK0zg.png',
                'name' => 'Red Velvet Swirl', 'category' => 'special', 'badge' => 'new',
                'price' => 4000, 'stock' => 12, 'status' => 'active',
                'description' => 'Glaze red velvet berwarna merah marun yang dalam menyelimuti donat ini. Pilinan krim keju putih membentuk spiral cantik di permukaannya. Remah kue red velvet bertaburan di atasnya untuk menambah tekstur lembut. Rasanya manis dengan sentuhan kakao ringan dan krim yang sedikit asam. Adonannya lembut dan lembap. Tampilannya elegan, cocok untuk hadiah atau acara spesial.',
            ],
            [
                'image' => 'Ip1VR6VxDHC2djMCvRwabB1wtUPC4EQlbVuhVPME.png',
                'name' => 'Midnight Oreo', 'category' => 'special', 'badge' => null,
                'price' => 4000, 'stock' => 15, 'status' => 'active',
                'description' => 'Permukaan donat ini ditutup penuh remah cokelat hitam yang pekat. Tiga garis krim putih membelahnya dan jadi kontras yang manis. Rasa cokelatnya kuat dan sedikit pahit, seperti kue cokelat yang dihancurkan. Teksturnya renyah di luar dan tetap empuk di dalam. Tampilannya gelap dan dramatis, beda dari donat lainnya. Cocok untuk kamu yang suka cokelat tanpa kompromi.',
            ],
            [
                'image' => 'IsOnVMSYxxT5bWPW4IvDd4nuQwhNsjpEqb97idDv.png',
                'name' => 'Caramel Biscoff', 'category' => 'special', 'badge' => 'best_seller',
                'price' => 4000, 'stock' => 20, 'status' => 'active',
                'description' => 'Glaze krim karamel yang lembut menjadi dasar donat yang satu ini. Di atasnya bertabur remah biskuit karamel yang renyah dan harum. Saus karamel dituang dalam garis-garis diagonal yang cantik. Rasanya manis dan sedikit gurih dengan aroma rempah yang halus. Perpaduan lembut dan renyah membuatnya sulit berhenti di satu gigitan. Salah satu favorit yang paling sering dipesan ulang.',
            ],
            [
                'image' => 'KcU29HBZkInmYRZprQblacbQWIL0aTJZ86u9bJ6f.png',
                'name' => 'Cookies & Cream', 'category' => 'special', 'badge' => null,
                'price' => 3000, 'stock' => 24, 'status' => 'active',
                'description' => 'Donat dengan lapisan cokelat lembut yang ditaburi remah cookies hitam melimpah. Garis krim putih menyilang di atasnya dengan manis yang lembut. Rasa cokelat dan krimnya berpadu seperti es krim cookies and cream favoritmu. Remah cookies memberi gigitan renyah di tengah adonan yang empuk. Tidak terlalu manis, jadi mudah dihabiskan. Cocok untuk semua penggemar rasa klasik.',
            ],
            [
                'image' => 'lJnJXashzKHHhUje6Umeyyc5uUMUzOQwPR9yP76K.png',
                'name' => 'Mocha Pearl', 'category' => 'special', 'badge' => null,
                'price' => 4000, 'stock' => 16, 'status' => 'active',
                'description' => 'Glaze krim mocha berwarna krem dengan aroma kopi yang lembut. Siraman cokelat membentuk garis-garis diagonal di permukaannya. Butiran mutiara cokelat mungil menghiasi atasnya dan renyah saat digigit. Rasanya manis dengan sedikit pahit kopi yang menyeimbangkan. Adonannya lembut dan ringan. Teman terbaik untuk secangkir kopi di sore hari.',
            ],
            [
                'image' => 'ZeS4PssUbhXqAi7vN81ivnRjDIPMChECn2doCTky.png',
                'name' => 'Raspberry Crumb', 'category' => 'special', 'badge' => 'new',
                'price' => 3000, 'stock' => 10, 'status' => 'active',
                'description' => 'Glaze krim vanila yang lembut menjadi dasar donat cantik ini. Di atasnya bertabur remah raspberry berwarna merah terang. Rasa manis asam yang segar dari raspberry memberi kejutan di setiap gigitan. Perpaduan krim yang lembut dan remah yang sedikit renyah terasa menyenangkan. Adonannya empuk dan lembap. Tampilannya cerah, enak untuk jadi penutup makan siang.',
            ],
        ];

        foreach ($items as $item) {
            $item['image'] = 'menu-items/' . $item['image'];
            $item['slug'] = Str::slug($item['name']);

            MenuItem::updateOrCreate(['image' => $item['image']], $item);
        }
    }
}