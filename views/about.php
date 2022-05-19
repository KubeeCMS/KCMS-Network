<?php
/**
 * About view.
 *
 * @since 2.0.0
 */
?>

<a class="wu-fixed wu-inline-block wu-bottom-0 wu-right-0 wu-bg-white wu-p-4 wu-rounded-full wu-shadow-sm wu-m-4 wu-no-underline" href="<?php echo esc_attr(network_admin_url()); ?>">
  <?php _e('&larr; Back to the Dashboard', 'wp-ultimo'); ?>
</a>

<div id="wp-ultimo-wrap" class="wrap">

  <div style="max-width: 730px;" class="wu-max-w-screen-md wu-mx-auto wu-my-10 wu-p-12 wu-bg-white wu-shadow wu-text-justify">

    <p class="wu-text-lg wu-leading-relaxed">
      It's finally here!
    </p>

    <h1 class="wu-text-3xl">
    Say hello to WP Ultimo 2.0!<br>Ahh, and this version is called <span class="wu-font-bold">Tim</span>.<br>
    <small class="wu-text-gray-500 wu-text-lg">(more on that later)</small>
    </h1>

    <p class="wu-text-lg wu-leading-relaxed">
      Hello everyone,
    </p>

    <p class="wu-text-lg wu-leading-relaxed">
      After a long, long time, countless cups of coffee and sleepless nights, WP Ultimo 2.0 is finally ready!
    </p>

    <p class="wu-text-lg wu-leading-relaxed">
      It was an incredible journey to get to this point, and I would like to <strong>thank you for your patience</strong> if you are one of the thousands of WP Ultimo customers that have been waiting for this update for years now.
    </p>

    <p class="wu-text-lg wu-leading-relaxed">
      Our previous version worked and solved the problem it was build to solve, but it did so in a very rigid manner. The goal with this new version is to set a solid and flexible foundation, capable of supporting improvements for the years to come.
    </p>

    <p class="wu-text-lg wu-leading-relaxed">
      This new version also marks the commencement of a new tradition.
    </p>

    <p class="wu-text-lg wu-leading-relaxed">
      WordPress core versions are named after jazz musicians and I had the privilege of getting to know and listen to a lot of great new sounds because WP introduced me to those names. The majority of our team is Brazilian and in an attempt to honor Brazilian music and hopefully get some of you to listen to some of our favorites, we'll be naming major and minor releases after incredibly talented Brazilian musicians.
    </p>

    <div class="wu-inline-block wu-float-right wu-ml-8 wu-mb-4">
      <img class="wu-block wu-rounded" src="https://wpultimo.com/wp-content/uploads/2020/06/tim-19-03-e1593385037865.jpg" width="230">
      <small class="wu-block wu-mt-1">Tim Maia</small>
    </div>

    <p class="wu-text-lg wu-leading-relaxed">
      That's why 2.0 is named <strong>Tim</strong>, after one of the greatest geniuses of the Brazilian soul music, Sebastião Rodrigues Maia, most commonly known as <a class="wu-no-underline" href="https://en.wikipedia.org/wiki/Tim_Maia" target="_blank">Tim Maia</a>.
    </p>

    <p class="wu-text-lg wu-leading-relaxed">Here's one my personal favorites - <a title="Tim Maia - O Descobridor dos Sete Mares" class="wu-no-underline" href="https://www.youtube.com/embed/PAUlCK8kuGU" target="_blank">O Descobridor dos Sete Mares</a>. It is played in EVERY graduation party in Brazil and every Brazilian knows the lyrics by heart.</p>

    <p class="wu-text-lg wu-leading-relaxed">If you like what you hear and want to hear more, check out <a class="wu-no-underline" href="https://www.youtube.com/playlist?list=PLZN-Qk4Q9ORAMZB-Ctv0wU48vrwdCQxC7" target="_blank">this playlist on YouTube</a>.</p>

    <p class="wu-text-lg wu-leading-relaxed">As always, let me know if you need anything!</p>

    <p class="wu-text-lg wu-leading-relaxed wu-mb-4">
      Yours truly,
    </p>

    <p class="wu-text-lg wu-leading-relaxed">

      <?php
      echo get_avatar('arindo@wpultimo.com', 64, '', 'Arindo Duque', array(
        'class' => 'wu-rounded-full'
      ));
      ?>

      <strong class="wu-block">Arindo Duque</strong>
      <small class="wu-block">Founder and CEO of NextPress, the makers of WP Ultimo</small>
    </p>

  </div>

  <div style="max-width: 700px;" class="wu-max-w-screen-md wu-mx-auto wu-mb-10">

    <hr class="hr-text wu-my-4 wu-text-gray-800" data-content="THIS VERSION WAS CRAFTED WITH LOVE BY">

    <?php

    $key_people = array(
      'arindo'  => array(
        'email'     => 'arindo@wpultimo.com',
        'signature' => 'arindo.png',
        'name'      => 'Arindo Duque',
        'position'  => 'Founder and CEO',
      ),
      'daniel'  => array(
        'email'     => 'daniel@wpultimo.com',
        'signature' => '',
        'name'      => 'Daniel Leal',
        'position'  => 'Developer',
      ),
      'felipe'  => array(
        'email'     => 'felipe@wpultimo.com',
        'signature' => '',
        'name'      => 'Felipe Elia',
        'position'  => '(former) Developer',
      ),
      'gustavo'  => array(
        'email'     => 'gustavo@wpultimo.com',
        'signature' => '',
        'name'      => 'Gustavo Modesto',
        'position'  => 'Developer',
      ),
      'juliana' => array(
        'email'     => 'juliana@wpultimo.com',
        'signature' => '',
        'name'      => 'Juliana Dias Gomes',
        'position'  => 'Do-it-all',
      ),
      'ramon'   => array(
        'email'     => 'ramon@wpultimo.com',
        'signature' => '',
        'name'      => 'Ramon Ahnert',
        'position'  => '(former) Developer',
      ),
      'rodinei'   => array(
        'email'     => 'rodinei@wpultimo.com',
        'signature' => '',
        'name'      => 'Rodinei Costa',
        'position'  => 'Developer',
      ),
      'ruel'    => array(
        'email'     => 'ruel@wpultimo.com',
        'signature' => '',
        'name'      => 'Ruel Porlas',
        'position'  => 'Support',
      ),
      'marcelo' => array(
        'email'     => 'marcelo@wpultimo.com',
        'signature' => '',
        'name'      => 'Marcelo Assis',
        'position'  => '(former) Developer',
      ),
    );

    ?>

    <div class="wu-flex wu-flex-wrap wu-mt-8">

      <?php foreach ($key_people as $person) : ?>

        <div class="wu-text-center wu-w-1/4 wu-mb-5">

          <?php
          echo get_avatar($person['email'], 64, '', 'Arindo Duque', array(
            'class' => 'wu-rounded-full'
          ));
          ?>
          <strong class="wu-text-base wu-block"><?php echo $person['name']; ?></strong>
          <small class="wu-text-xs wu-block"><?php echo $person['position']; ?></small>

        </div>

      <?php endforeach; ?>

    </div>

  </div>

</div>

<style>
.hr-text {
  line-height: 1em;
  position: relative;
  outline: 0;
  border: 0;
  /* color: black; */
  text-align: center;
  height: 1.5em;
  opacity: .5;
}
.hr-text:before {
  content: '';
  background: -webkit-gradient(linear, left top, right top, from(transparent), color-stop(#818078), to(transparent));
  background: linear-gradient(to right, transparent, #818078, transparent);
  position: absolute;
  left: 0;
  top: 50%;
  width: 100%;
  height: 1px;
}
.hr-text:after {
  content: attr(data-content);
  position: relative;
  display: inline-block;
  /* color: black; */
  padding: 0 .5em;
  line-height: 1.5em;
  color: #818078;
  background-color: #eef2f5;
}
</style>
