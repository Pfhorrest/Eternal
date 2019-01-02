-- Eternal X Omega HUD version 1.0pre
-- by Jeremiah Morris and Forrest Cameranesi

-- swirl animation speed when standing still, in degrees per tick
anim_swirl_standing = 0.5
-- swirl animation speed when moving at max speed
anim_swirl_running = 3

-- slide animation speed, in ticks to finish (when first drawing a weapon)
anim_slide_duration = 15

-- rise/fall reaction parameters
anim_fall_maxoffset = 15
anim_fall_minoffset = -5
anim_fall_pow = 2
anim_fall_mult = -0.0015


-- classic weapon rect: (398,353) - (610,455)
wdef = {}
wdef["pistol"] = { left=404-398, top=362-353 }
wdef["pistol"].primary = { display="energy", left=570-398, top=362-353, width=28, height=56 }
wdef["pistol"].secondary = { display="energy", left=411-398, top=362-353, width=28, height=56 }
wdef["blades"] = { left=404-398, top=362-353 }
wdef["blades"].primary = { display="energy", left=570-398, top=362-353, width=28, height=56 }
wdef["blades"].secondary = { display="energy", left=411-398, top=362-353, width=28, height=56 }
wdef["fusion cannon"] = { left=404-398, top=362-353 }
wdef["fusion cannon"].primary = { display="energy", left=570-398, top=362-353, width=28, height=56 }
wdef["staff"] = { left=404-398, top=362-353 }
wdef["staff"].primary = { display="energy", left=570-398, top=362-353, width=28, height=56 }
wdef["napalm cannon"] = { left=404-398, top=362-353 }
wdef["napalm cannon"].primary = { display="energy", left=570-398, top=362-353, width=28, height=56 }
wdef["wave motion cannon"] = { left=404-398, top=362-353 }
wdef["wave motion cannon"].primary = { display="energy", left=570-398, top=362-353, width=28, height=56 }
wdef["havoc rifle"] = { left=404-398, top=362-353 }
wdef["havoc rifle"].primary = { display="ammo", left=542-398, top=362-353, across=14, down=6, delta_x=4, delta_y=7 }
wdef["havoc rifle"].secondary = { display="ammo", left=542-398, top=404-353, across=7, down=1, delta_x=8, delta_y=14 }
wdef["scatter rifle"] = { left=404-398, top=362-353 }
wdef["scatter rifle"].primary = { display="ammo", left=542-398, top=362-353, across=14, down=8, delta_x=4, delta_y=7 }
wdef["ball"] = { left=480-398, top=362-353 }


Triggers = {}
function Triggers.draw()

  if Player.dead then return end
  env_light_setup()
  
  drawLevelName()
  
  -- sliding animation
  local slide_max  = li["bg"].height + anchors["ly"]
  do
    local slide_delta = slide_max / anim_slide_duration
    local slide_ticks = math.max(0, Game.ticks - anim_slide_lasttick)
    anim_slide_lasttick = Game.ticks
  
    if not Player.weapons.desired then
      anim_slide_offset = math.min(slide_max, anim_slide_offset + (slide_delta * slide_ticks))
    else
      anim_slide_offset = math.max(0, anim_slide_offset - (slide_delta * slide_ticks))
    end
  end    
  
  -- rise/fall "jiggle"
  local velocity_offset = 0
  if Player.velocity then
    local vel = math.pow(math.abs(Player.velocity.vertical) * 1024, anim_fall_pow)
    if Player.velocity.vertical < 0 then
      vel = -vel
    end
    velocity_offset = math.min(math.max(vel * anim_fall_mult, anim_fall_minoffset), anim_fall_maxoffset)
  end
  
  -- CENTER
  do
    local ax = anchors["cx"]
    local ay = anchors["cy"]
    
    -- 01: background
    local image = ci["bg"]
    env_draw(image, ax - (image.width / 2), ay)
    
    -- 02: compass beacon
    if Player.motion_sensor.active then
      local image = ci["quad"]
      local wh = math.floor(image.width / 2)
      local hh = math.floor(image.height / 2)
      local x = ax - wh
      local y = ay
      if Player.compass.nw then
        image.crop_rect.x = 0
        image.crop_rect.y = 0
        image.crop_rect.width = wh
        image.crop_rect.height = hh
        env_draw_glow(image, x, y)
      end
      if Player.compass.ne then
        image.crop_rect.x = wh
        image.crop_rect.y = 0
        image.crop_rect.width = wh
        image.crop_rect.height = hh
        env_draw_glow(image, x, y)
      end
      if Player.compass.sw then
        image.crop_rect.x = 0
        image.crop_rect.y = hh
        image.crop_rect.width = wh
        image.crop_rect.height = hh
        env_draw_glow(image, x, y)
      end
      if Player.compass.se then
        image.crop_rect.x = wh
        image.crop_rect.y = hh
        image.crop_rect.width = wh
        image.crop_rect.height = hh
        env_draw_glow(image, x, y)
      end
    end
    
    -- 03: mic
    if Player.microphone_active then
      image = ci["mic_on"]
    else
      image = ci["mic_off"]
    end
    env_draw(image, ax - (image.width / 2), ay)
    
    -- 04: swirl
    image = ci["swirl"]
    if opengl then
      local speedfrac = 0
      if Player.velocity then
        speedfrac = math.min(1, math.abs(Player.velocity.forward) / .125)
      end
      local speed = anim_swirl_standing + (speedfrac * (anim_swirl_running - anim_swirl_standing))
      local elapsed = Game.ticks - anim_swirl_lasttick
      if elapsed < 0 then
        elapsed = 0
      end
      anim_swirl_lasttick = Game.ticks
      anim_swirl_lastrot = (anim_swirl_lastrot + (elapsed * speed)) % 360
      image.rotation = anim_swirl_lastrot
    end
    env_draw(image, ax - (image.width / 2), ay)
    
    -- 05: blips
    if Player.motion_sensor.active then
      local sens_rad = 60
      local sens_xcen = ax
      local sens_ycen = ay + 85
      for i = 1,#Player.motion_sensor.blips do
        local blip = Player.motion_sensor.blips[i - 1]
        local mult = blip.distance * sens_rad / 8.0
        local rad = math.rad(blip.direction)
        local xoff = sens_xcen + math.cos(rad) * mult
        local yoff = sens_ycen + math.sin(rad) * mult
        
        if not (bi[blip.type.mnemonic] == nil) then
          image = bi[blip.type.mnemonic][blip.intensity]
  --        env_draw_glow(
          image:draw(xoff - math.floor(image.width/2), yoff - math.floor(image.height/2))
        else
          error("Unrecognized blip type: " .. blip.type.mnemonic)
        end
      end
    end
    
    -- 06: compass directions
    local compass_rot = (360 - Player.direction) % 360
    if opengl then
      image = ci["compass"]
      image.rotation = compass_rot
      env_draw_glow(image, ax - (image.width / 2), ay)
    end
    
    -- 07: glass / outer ring
    image = ci["glass"]
    env_draw(image, ax - (image.width / 2), ay)
    
    -- 08: ring detail (spins with compass)
    image = ci["ring"]
    if opengl then
      image.rotation = compass_rot
    end
    env_draw(image, ax - (image.width / 2), ay)
  end
  
  -- LEFT
  do
    local ax = anchors["lx"]
    local ay = math.floor(anchors["ly"] - anim_slide_offset - velocity_offset)

    if anim_slide_offset < slide_max then
      -- 01: background
      local image = li["bg"]
      env_draw(image, ax, ay)
      
      -- 02: text (start mask)
      local tx = ax + 30
      local ty = ay + 20
      local tw = 200
      local th = 120
      image = li["mask"]
      if opengl then
        Screen.masking_mode = "drawing"
        env_draw(image, ax, ay)
        Screen.masking_mode = "enabled"
      else
        Screen.clip_rect.x = tx
        Screen.clip_rect.y = ty
        Screen.clip_rect.width = tw
        Screen.clip_rect.height = th
      end
      
      local sec = Player.inventory_sections.current
      
      -- 02: text (player name)
      do
        local pname = "Marcus Jones"
        if #Game.players > 1 then
          for i = 1,#Game.players do
            if Game.players[i].local_ then
              pname = Game.players[i].name
            end
          end
        end
        local nw, nh = fbold:measure_text(pname)
        fbold:draw_text(pname, tx + (tw - nw)/2, ay + 25, fcolor)
      end
      
      -- 02: text (inventory)
      drawInventory({ x = tx, y = ay + 40, w = tw, h = 100 })
      
      -- 02: text (end mask)
      if opengl then
        Screen.masking_mode = "disabled"
      else
        Screen.clip_rect.x = 0
        Screen.clip_rect.y = 0
        Screen.clip_rect.width = Screen.width
        Screen.clip_rect.height = Screen.height
      end
      
      -- 03: glass
      image = li["glass"]
      env_draw(image, ax, ay)
    end
    
    ay = math.max(ay, anchors["cy"] - 146 + 24)
    
    -- 04: oxygen
    draw_bar(li["oxygen"][0], ax + 39, ay + 146, Player.oxygen, 0)
    draw_bar(li["oxygen"][1], ax + 39, ay + 146, Player.oxygen, 10800)
    
    -- 05: oxygen glass
    image = li["oxygen_glass"]
    env_draw(image, ax, ay)
    
  end

  -- RIGHT
  do
    local ax = anchors["rx"]
    local ay = math.floor(anchors["ry"] - anim_slide_offset - velocity_offset)
    
    if anim_slide_offset < slide_max then
      -- 01: background
      local image = ri["bg"]
      env_draw(image, ax - image.width, ay)
      
      -- 02: text (start mask)
      local tx = ax - image.width + 40
      local ty = ay + 20
      local tw = 200
      local th = 120
      image = ri["mask"]
      if opengl then
        Screen.masking_mode = "drawing"
        env_draw(image, ax - image.width, ay)
        Screen.masking_mode = "enabled"
      else
        Screen.clip_rect.x = tx
        Screen.clip_rect.y = ty
        Screen.clip_rect.width = tw
        Screen.clip_rect.height = th
      end
      
      -- 02: text (status field)
      do
        local status = "Mjolnir Mk X"
        local nw, nh = fbold:measure_text(status)
        fbold:draw_text(status, tx + (tw - nw)/2, ay + 25, fcolor)
      end
      
      -- 02: weapons
      if Player.weapons.desired then
        local weapon = Player.weapons.desired
        
        if weapon then
          drawWeapon(weapon, { x = tx, y = ay + 40, w = tw, h = 100 })

          -- weapon text
          local wname = Player.weapons.desired.name
          if wname then        
            local nw, nh = fbold:measure_text(wname)
            fbold:draw_text(wname, math.floor(tx + (tw - nw)/2), ay + 120, fcolor)
          end
        end
        
      end
      
      -- 02: text (end mask)
      if opengl then
        Screen.masking_mode = "disabled"
      else
        Screen.clip_rect.x = 0
        Screen.clip_rect.y = 0
        Screen.clip_rect.width = Screen.width
        Screen.clip_rect.height = Screen.height
      end
      
      -- 03: glass
      image = ri["glass"]
      env_draw(image, ax - image.width, ay)
    end
    
    ay = math.max(ay, anchors["cy"] - 130)
    
    -- 04: energy
    local health = Player.energy
    local hx = ax - 219
    local hy = ay + 146
    draw_bar(ri["energy"][0], hx, hy, health, 0)
    draw_bar(ri["energy"][1], hx, hy, health, 150)
    draw_bar(ri["energy"][2], hx, hy, health - 150, 150)
    draw_bar(ri["energy"][3], hx, hy, health - 300, 150)
    
    -- 05: energy glass
    image = ri["energy_glass"]
    env_draw(image, ax - image.width, ay)
    
  end
      
end

function adj(number)
  return math.floor(number)
end
function scaled(number)
  return adj(number * scale)
end

function Triggers.resize()

  Screen.clip_rect.width = Screen.width
  Screen.clip_rect.x = 0
  Screen.clip_rect.height = Screen.height
  Screen.clip_rect.y = 0

  Screen.map_rect.width = Screen.width
  Screen.map_rect.x = 0
  Screen.map_rect.height = Screen.height
  Screen.map_rect.y = 0
  
  scale = 1.0
  sh = math.min(Screen.height, math.max(480, (Screen.width / 2) + 160))
  sw = math.min(Screen.width, sh*2)
  sx = (Screen.width - sw)/2
  sy = (Screen.height - sh)/2
  
  Screen.world_rect.width = sw
  Screen.world_rect.x = sx
  Screen.world_rect.height = sh
  Screen.world_rect.y = sy
  
  local smargin = math.floor((sh - 480) / 10)
  anchors = { }
  anchors["cy"] = math.min(sy + smargin, sy + sh - (320 + ci["bg"].height))
  anchors["ly"] = anchors["cy"]
  anchors["ry"] = anchors["cy"]
  anchors["cx"] = Screen.world_rect.x + (sw / 2)
  anchors["lx"] = math.min(anchors["cx"] - 330, sx + smargin)
  anchors["rx"] = math.max(anchors["cx"] + 330, sx + sw - smargin)
  
  local th = math.max(320, sh - (anchors["cy"] + ci["bg"].height - sy))
  local tw = math.max(640, sw)
  local h = math.min(tw / 2, th)
  local w = h*2
  Screen.term_rect.width = w
  Screen.term_rect.x = sx + (sw - w)/2
  Screen.term_rect.height = h
  Screen.term_rect.y = sy + sh - th + (th - h)/2
  
  pos = {}
  pos.weapon_sprites = {}
  pos.weapon_ammo = {}
  for k in pairs(wdef) do
    local w = wdef[k]
    
    if img.weapons[k] then
      pos.weapon_sprites[k] = { x = scaled(w.left), y = scaled(w.top) }
      if img.weapons[k .. " left"] then
        pos.weapon_sprites[k .. " left"] = { x = pos.weapon_sprites[k].x + scaled(w.mult_delta_x or 0), y = pos.weapon_sprites[k].y + scaled(w.mult_delta_y or 0) }
      end
    end
    
    for i, v in ipairs({ w.primary, w.secondary }) do
      if not v then return end
      if v.display == "ammo" then
        pos.weapon_ammo[k .. " ammo " .. i] = { x = scaled(v.left), y = scaled(v.top), bullets = v.across, rows = v.down, w = (v.delta_x * scale), h = scaled(v.delta_y), rtl = v.rtl }
      elseif v.display == "energy" then
        pos.weapon_ammo[k .. " energy " .. i] = { x = scaled(v.left), y = scaled(v.top), w = scaled(v.width), h = scaled(v.height) }
      end
    end

  end
  
end

function Triggers.init()
  
  -- align weapon and item mnemonics
  ItemTypes["knife"].mnemonic = "fist"
  
  local wep_aliases = {}
  wep_aliases["shotgun"] = "blades"
  wep_aliases["fusion pistol"] = "fusion cannon"
  wep_aliases["assault rifle"] = "staff"
  wep_aliases["alien weapon"] = "napalm cannon"
  wep_aliases["smg"] = "wave motion cannon"
  wep_aliases["missile launcher"] = "havoc rifle"
  wep_aliases["flamethrower"] = "scatter rifle"
  for k, v in pairs(wep_aliases) do
    ItemTypes[k].mnemonic = v
    WeaponTypes[k].mnemonic = v
    ItemTypes[k .. " ammo"].mnemonic = v .. " ammo"
  end

  colortable = { slate  = { 0.14, 0.37, 0.64, 1.0 },
                 red    = { 1.00, 0.00, 0.00, 1.0 },
                 violet = { 0.69, 0.00, 0.37, 1.0 },
                 yellow = { 1.00, 1.00, 0.00, 1.0 },
                 white  = { 0.92, 0.92, 0.92, 1.0 },
                 orange = { 0.96, 0.34, 0.00, 1.0 },
                 blue   = { 0.05, 0.00, 1.00, 1.0 },
                 green  = { 0.00, 1.00, 0.00, 1.0 } }
                 

  opengl = true
  if Screen.renderer == "software" then
    opengl = false
  end
  
  anim_swirl_lasttick = 0
  anim_swirl_lastrot  = 0
  
  anim_slide_lasttick  = 0
  anim_slide_offset    = 1000
  
  ci = { }
  do
    ci["bg"] = Images.new{path = "HUD/center/01_background.png"}
    ci["quad"] = Images.new{path = "HUD/center/02_beacon.png"}
    ci["mic_on"] = Images.new{path = "HUD/center/03_mic_on.png"}
    ci["mic_off"] = Images.new{path = "HUD/center/03_mic_off.png"}
    ci["swirl"] = Images.new{path = "HUD/center/04_swirl.png"}
    ci["compass"] = Images.new{path = "HUD/center/06_directions.png"}
    ci["glass"] = Images.new{path = "HUD/center/07_glass.png"}
    ci["ring"] = Images.new{path = "HUD/center/08_ring.png"}
  end
  
  bi = { }
  do
    bi["alien"] = { }
    bi["alien"][0] = Images.new{path = "HUD/center/05_blips/enemy0.png"}
    bi["alien"][1] = Images.new{path = "HUD/center/05_blips/enemy1.png"}
    bi["alien"][2] = Images.new{path = "HUD/center/05_blips/enemy2.png"}
    bi["alien"][3] = Images.new{path = "HUD/center/05_blips/enemy3.png"}
    bi["alien"][4] = Images.new{path = "HUD/center/05_blips/enemy4.png"}
    bi["alien"][5] = Images.new{path = "HUD/center/05_blips/enemy5.png"}
    bi["friend"] = { }
    bi["friend"][0] = Images.new{path = "HUD/center/05_blips/friend0.png"}
    bi["friend"][1] = Images.new{path = "HUD/center/05_blips/friend1.png"}
    bi["friend"][2] = Images.new{path = "HUD/center/05_blips/friend2.png"}
    bi["friend"][3] = Images.new{path = "HUD/center/05_blips/friend3.png"}
    bi["friend"][4] = Images.new{path = "HUD/center/05_blips/friend4.png"}
    bi["friend"][5] = Images.new{path = "HUD/center/05_blips/friend5.png"}
    bi["hostile player"] = { }
    bi["hostile player"][0] = Images.new{path = "HUD/center/05_blips/hostile0.png"}
    bi["hostile player"][1] = Images.new{path = "HUD/center/05_blips/hostile1.png"}
    bi["hostile player"][2] = Images.new{path = "HUD/center/05_blips/hostile2.png"}
    bi["hostile player"][3] = Images.new{path = "HUD/center/05_blips/hostile3.png"}
    bi["hostile player"][4] = Images.new{path = "HUD/center/05_blips/hostile4.png"}
    bi["hostile player"][5] = Images.new{path = "HUD/center/05_blips/hostile5.png"}
  end
  
  li = { }
  do
    li["bg"] = Images.new{path = "HUD/left/01_background.png"}
    li["mask"] = Images.new{path = "HUD/left/02_text_mask.png"}
    li["glass"] = Images.new{path = "HUD/left/03_glass.png"}
    li["oxygen"] = { }
    li["oxygen"][0] = Images.new{path = "HUD/left/04_oxygen_0x.png"}
    li["oxygen"][1] = Images.new{path = "HUD/left/04_oxygen_1x.png"}
    li["oxygen_glass"] = Images.new{path = "HUD/left/05_oxygen_glass.png"}
  end

  ri = { }
  do
    ri["bg"] = Images.new{path = "HUD/right/01_background.png"}
    ri["mask"] = Images.new{path = "HUD/right/02_text_mask.png"}
    ri["glass"] = Images.new{path = "HUD/right/03_glass.png"}
    ri["energy"] = { }
    ri["energy"][0] = Images.new{path = "HUD/right/04_energy_0x.png"}
    ri["energy"][1] = Images.new{path = "HUD/right/04_energy_1x.png"}
    ri["energy"][2] = Images.new{path = "HUD/right/04_energy_2x.png"}
    ri["energy"][3] = Images.new{path = "HUD/right/04_energy_3x.png"}
    ri["energy_glass"] = Images.new{path = "HUD/right/05_energy_glass.png"}
  end
  
  img = {}
  img.weapons = {}
  img.weapons["pistol"] = Images.new{path = "HUD/right/weapons/pistol.png"}
  img.weapons["pistol left"] = Images.new{path = "HUD/right/weapons/pistol_left.png"}
  img.weapons["pistol left dis"] = Images.new{path = "HUD/right/weapons/pistol_left_dis.png"}
  img.weapons["blades"] = Images.new{path = "HUD/right/weapons/blades.png"}
  img.weapons["blades left"] = Images.new{path = "HUD/right/weapons/blades_left.png"}
  img.weapons["blades left dis"] = Images.new{path = "HUD/right/weapons/blades_left_dis.png"}
  img.weapons["fusion cannon"] = Images.new{path = "HUD/right/weapons/fusion_cannon.png"}
  img.weapons["staff"] = Images.new{path = "HUD/right/weapons/staff.png"}
  img.weapons["napalm cannon"] = Images.new{path = "HUD/right/weapons/napalm_cannon.png"}
  img.weapons["wave motion cannon"] = Images.new{path = "HUD/right/weapons/wave_motion_cannon.png"}
  img.weapons["havoc rifle"] = Images.new{path = "HUD/right/weapons/havoc_rifle.png"}
  img.weapons["scatter rifle"] = Images.new{path = "HUD/right/weapons/scatter_rifle.png"}
  
  img.ammo = {}
  img.ammo["havoc rifle 1 full"] = Images.new{path = "HUD/right/weapons/ammo_small_full.png"}
  img.ammo["havoc rifle 1 empty"] = Images.new{path = "HUD/right/weapons/ammo_small_empty.png"}
  img.ammo["havoc rifle 2 full"] = Images.new{path = "HUD/right/weapons/ammo_large_full.png"}
  img.ammo["havoc rifle 2 empty"] = Images.new{path = "HUD/right/weapons/ammo_large_empty.png"}
  img.ammo["scatter rifle 1 full"] = Images.new{path = "HUD/right/weapons/ammo_small_full.png"}
  img.ammo["scatter rifle 1 empty"] = Images.new{path = "HUD/right/weapons/ammo_small_empty.png"}
  
  fbold = Fonts.new{id = 4, size = 9, style = 1}
  fnorm = Fonts.new{id = 4, size = 9, style = 0}
  flevel = Fonts.new{id = 4, size = 18, style = 0}
  fwidth, fheight = fnorm:measure_text(" ")
  fheight = 11
  flwidth, flheight = flevel:measure_text(" ")
  fcolor = { 0, 1, 0, 1 }
  
  Triggers.resize()
end

function draw_bar(bar, x, y, cur, max)
  if cur >= max then
    -- draw completely full bar, cap is ignored
    bar.crop_rect.x = 0
    bar.crop_rect.width = bar.width
    env_draw_glow(bar, x, y)
  elseif cur > 0 then
    -- determine size of bar
    local w = math.floor(bar.width * cur / max)
    local wh = math.floor(w / 2)
    
    -- crop left and right sides equally
    bar.crop_rect.x = 0
    bar.crop_rect.width = wh
    env_draw_glow(bar, x, y)
    bar.crop_rect.x = bar.width - (w - wh)
    bar.crop_rect.width = w - wh
    env_draw_glow(bar, x + wh, y)
  end
end

function env_light_setup()
  local ambient = Lighting.ambient_light
  local weapon = Lighting.weapon_flash
  local combined = math.min(1, ambient*2 + weapon)
  if weapon > ambient then
    combined = math.min(1, weapon*2 + ambient)
  end
  env_level = 0.5 + combined/2
  env_level_glow = 1 -- 0.75 + combined/4
  env_level_halfglow = 0.67 + combined/3
  
  env_color = nil
  env_color_glow = nil
  env_color_halfglow = nil
  if Lighting.liquid_fader.active and (Lighting.liquid_fader.type == "soft tint") then
    env_color = Lighting.liquid_fader.color
    env_color.a = env_color.a*0.67
    env_level = env_level * (1 - env_color.a)
    env_color.r = env_color.r * env_color.a
    env_color.g = env_color.g * env_color.a
    env_color.b = env_color.b * env_color.a
    
    env_color_glow = Lighting.liquid_fader.color
    env_color_glow.a = env_color_glow.a*0.33
    env_level_glow = env_level_glow * (1 - env_color_glow.a)
    env_color_glow.r = env_color_glow.r * env_color_glow.a
    env_color_glow.g = env_color_glow.g * env_color_glow.a
    env_color_glow.b = env_color_glow.b * env_color_glow.a

    env_color_halfglow = Lighting.liquid_fader.color
    env_color_halfglow.a = env_color_halfglow.a*0.5
    env_level_halfglow = env_level_halfglow * (1 - env_color_halfglow.a)
    env_color_halfglow.r = env_color_halfglow.r * env_color_halfglow.a
    env_color_halfglow.g = env_color_halfglow.g * env_color_halfglow.a
    env_color_halfglow.b = env_color_halfglow.b * env_color_halfglow.a
  end
  
  env_damage_hide = false
  env_damage_static = 0
  if Lighting.damage_fader.active then
    local dcolor = Lighting.damage_fader.color
    local dtype = Lighting.damage_fader.type.mnemonic
    
    if not ((dtype == "tint") and (dcolor.r == 0) and (dcolor.g == 1) and (dcolor.b == 0)) then
    
--      env_wstatic = env_wstatic + 1
--      if not wstatic[env_wstatic] then
--        env_wstatic = 1
--      end
      
      if (dtype == "tint") or (dtype == "soft tint") then
        env_damage_static = math.min(1, dcolor.a*1.3)
        if math.random() < (dcolor.a) then
          env_damage_hide = true
        end
      elseif (dtype == "negate") then
        env_damage_static = math.min(1, dcolor.a*1.3)
        if math.random() < (dcolor.a*1.5) then
          env_damage_hide = true
        end
      elseif (dtype == "dodge") or (dtype == "burn") then
        env_damage_static = dcolor.a*dcolor.a
        if math.random() < (dcolor.a/3) then
          env_damage_hide = true
        end
      elseif (dtype == "randomize") then
        env_damage_static = math.min(1, dcolor.a*2)
        if math.random() < (dcolor.a*3) then
          env_damage_hide = true
        end
      end
    
    end
  end
end

function env_draw(img, x, y)
  if not img then return end
  tint_color = img.tint_color
  img.tint_color = env_adjust({ tint_color.r, tint_color.g, tint_color.b, tint_color.a })
  if img.tint_color.a > 0.01 then
    img:draw(x, y)
  end
  img.tint_color = tint_color
end
function env_draw_glow(img, x, y)
  if not img then return end
  tint_color = img.tint_color
  img.tint_color = env_adjust_glow({ tint_color.r, tint_color.g, tint_color.b, tint_color.a })
  if img.tint_color.a > 0.01 then
    img:draw(x, y)
  end
  img.tint_color = tint_color
end
function env_draw_halfglow(img, x, y)
  if not img then return end
  tint_color = img.tint_color
  img.tint_color = env_adjust_halfglow({ tint_color.r, tint_color.g, tint_color.b, tint_color.a })
  if img.tint_color.a > 0.01 then
    img:draw(x, y)
  end
  img.tint_color = tint_color
end

function env_adjust(color)
  color[1] = color[1] * env_level
  color[2] = color[2] * env_level
  color[3] = color[3] * env_level
  if env_color then
    color[1] = color[1] + env_color.r
    color[2] = color[2] + env_color.g
    color[3] = color[3] + env_color.b
  end
  return color
end
function env_adjust_glow(color)
  color[1] = color[1] * env_level_glow
  color[2] = color[2] * env_level_glow
  color[3] = color[3] * env_level_glow
  if env_color_glow then
    color[1] = color[1] + env_color_glow.r
    color[2] = color[2] + env_color_glow.g
    color[3] = color[3] + env_color_glow.b
  end
  return color
end
function env_adjust_halfglow(color)
  color[1] = color[1] * env_level_halfglow
  color[2] = color[2] * env_level_halfglow
  color[3] = color[3] * env_level_halfglow
  if env_color_halfglow then
    color[1] = color[1] + env_color_halfglow.r
    color[2] = color[2] + env_color_halfglow.g
    color[3] = color[3] + env_color_halfglow.b
  end
  return color
end

function drawInventory(rect)
  local off = scaled(2)
  local x = sx + rect.x
  local y = sy + rect.y
  local w = rect.w
  local h = fheight
  local lasty = y + (9 * h)
  local clr = fcolor
  local sec = Player.inventory_sections.current
  
  -- header
--  Screen.fill_rect(x, y, w, h, { 0, 0.2, 0, 1 })
  local extra = nil
  if sec.type == "network statistics" then
    draw_text_right(fbold, net_header(), x + w - off, y, clr)
  end
  fbold:draw_text(sec.name, x + off, y, clr)
  y = y + h
  
  local ctx = fnorm:measure_text("999")
  ctx = ctx + x + off
  local inx = fbold:measure_text("999")
  inx = inx + x + off + off
  
  if sec.type == "network statistics" then
    -- player list and rankings
    local all_players = sorted_players()
    local gametype = Game.type
    if gametype == "netscript" then
      gametype = Game.scoring_mode
    end

    inx = x + off
    ctx = x + w - off

    for i = 1,#all_players do
      local p = all_players[i]
      local score = ranking_text(gametype, p.ranking)
      draw_text_right(fbold, score, ctx, y, colortable[p.team.mnemonic])
      fbold:draw_text(p.name, inx, y, colortable[p.color.mnemonic])
      
      y = y + h
      if y >= lasty then break end
    end
  else
    for i = 1,#ItemTypes do
      local item = Player.items[i - 1]
      local name = ItemTypes[i - 1]
      if (item.count > 0 and item.inventory_section == sec.type) and not (name == "fist") then
        local ct = string.format("%d", item.count)
        draw_text_right(fnorm, ct, ctx, y, clr)
        
        local iname
        if item.count == 1 then
          iname = item.singular
        else
          iname = item.plural
        end
        
        fbold:draw_text(string.sub(iname, 1, 22), inx, y, clr)
        
        y = y + h
        if y >= lasty then break end
      end
    end
  end
end

function draw_text_right(font, text, x, y, color)
  if text == nil then return end
  local tx, ty = font:measure_text(text)
  font:draw_text(text, x - tx, y, color)
end

function draw_text_center(font, text, x, y, color)
  if text == nil then return end
  local tx, ty = font:measure_text(text)
  font:draw_text(text, x - tx/2, y, color)
end

function clip(rect)
  Screen.clip_rect.x = sx + rect.x
  Screen.clip_rect.y = sy + rect.y
  Screen.clip_rect.width = rect.w
  Screen.clip_rect.height = rect.h
end

function unclip()
  clip({ x = 0, y = 0, w = sw, h = sh })
end

function drawWeapon(weapon, rect)
  local wt = weapon.type.mnemonic
  local image = img.weapons[wt]
  if not image then return end
  
  local wp = pos.weapon_sprites[wt]
  image:draw(wp.x + rect.x, wp.y + rect.y)
  
  drawAmmo(rect, pos.weapon_ammo[wt .. " ammo 1"], weapon.primary.rounds, img.ammo[wt .. " 1 full"], img.ammo[wt .. " 1 empty"])
  drawEnergy(rect, pos.weapon_ammo[wt .. " energy 1"], weapon.primary.rounds / weapon.primary.total_rounds)
  
  wp = pos.weapon_sprites[wt .. " left"]
  if wp then
    if Player.items[wt].count > 1 then
      image = img.weapons[wt .. " left"]
    else
      image = img.weapons[wt .. " left dis"]
    end
    if image then
      image:draw(wp.x + rect.x, wp.y + rect.y)
    end
  end
  
  drawAmmo(rect, pos.weapon_ammo[wt .. " ammo 2"], weapon.secondary.rounds, img.ammo[wt .. " 2 full"], img.ammo[wt .. " 2 empty"])
  drawEnergy(rect, pos.weapon_ammo[wt .. " energy 2"], weapon.secondary.rounds / weapon.secondary.total_rounds)
end

function drawAmmo(rect, info, rounds, image_full, image_empty)
  if not info then return end
  if not image_full then return end
  if not image_empty then return end
  
  local x = info.x + rect.x
  local y = info.y + rect.y
  local drawn = 0
  for r = 1,info.rows do
    image_empty:draw(x, y)
    if rounds > drawn then
      local to_draw = math.min(rounds - drawn, info.bullets)
      local draw_w = adj(info.w * to_draw)
      local draw_x = x
      image_full.crop_rect.width = draw_w
      if info.rtl then
        local offx = adj(info.w * (info.bullets - to_draw))
        image_full.crop_rect.x = offx
        draw_x = x + offx
      end
      image_full:draw(draw_x, y)
      drawn = drawn + to_draw
    end
    y = y + info.h
  end
end

function drawEnergy(rect, info, frac)
  if not info then return end
  
  local x = rect.x + info.x
  local y = rect.y + info.y
  local m = scaled(1)
  Screen.fill_rect(x, y, m, info.h, { 0, 1, 0, 1 })
  Screen.fill_rect(x + info.w - m, y, m, info.h, { 0, 1, 0, 1 })
  Screen.fill_rect(x + m, y, info.w - (2 * m), m, { 0, 1, 0, 1 })
  Screen.fill_rect(x + m, y + info.h - m, info.w - (2 * m), m, { 0, 1, 0, 1 })
  
  local totalh = info.h - (2 * m)
  local h = adj(math.max(0, math.min(frac, 1)) * totalh)
  Screen.fill_rect(x + m, y + m,
                   info.w - (2 * m), totalh - h,
                   { 0, 0.1, 0, 0.6 })
  if frac > 0.0 then
    Screen.fill_rect(x + m, y + m + (totalh - h),
                     info.w - (2 * m), h,
                     { 0, 1, 0, 0.6 })
  end
end

function drawLevelName()

  local cur_ticks = Game.ticks
  local cur_level = Level.index
  if (cur_level ~= last_level) or (cur_ticks < last_ticks) then
    last_level_start_ticks = cur_ticks
    last_level = cur_level
  end
  last_ticks = cur_ticks

  local elapsed = cur_ticks - last_level_start_ticks
  if elapsed >= 270 then return end
  
  local alpha = 0
  if elapsed < 30 then
    alpha = 0
  elseif elapsed < 90 then
    alpha = (elapsed - 30)/60
  elseif elapsed < 150 then
    alpha = 1
  else
    alpha = 1 - (elapsed - 150)/120
  end
  
  if alpha > 0 then
    draw_text_center(flevel, Level.name, sx + math.floor(sw / 2), sy + sh - (3*flheight*scale), { 1, 1, 1, alpha})
  end
end


function format_time(ticks)
   local secs = math.ceil(ticks / 30)
   return string.format("%d:%02d", math.floor(secs / 60), secs % 60)
end


function format_time(ticks)
   local secs = math.ceil(ticks / 30)
   return string.format("%d:%02d", math.floor(secs / 60), secs % 60)
end

function net_header()
  if Game.time_remaining then
    return format_time(Game.time_remaining)
  end
  if Game.kill_limit then
    local max_kills = 0
    for i = 1,#Game.players do
      max_kills = math.max(max_kills, Game.players[i - 1].kills)
    end
    return string.format("%d", Game.kill_limit - max_kills)
  end
  return nil
end

function ranking_text(gametype, ranking)
  if (gametype == "kill monsters") or
     (gametype == "capture the flag") or
     (gametype == "rugby") or
     (gametype == "most points") then
    return string.format("%d", ranking)
  end
  if (gametype == "least points") then
    return string.format("%d", -ranking)
  end
  if (gametype == "cooperative play") then
    return string.format("%d%%", ranking)
  end
  if (gametype == "most time") or
     (gametype == "least time") or
     (gametype == "king of the hill") or
     (gametype == "kill the man with the ball") or
     (gametype == "defense") or
     (gametype == "tag") then
    return format_time(math.abs(ranking))
  end
  
  -- unknown
  return nil
end

function comp_player(a, b)
  if a.ranking > b.ranking then
    return true
  end
  if a.ranking < b.ranking then
    return false
  end
  if a.name < b.name then
    return true
  end
  return false
end

function sorted_players()
  local tbl = {}
  for i = 1,#Game.players do
    table.insert(tbl, Game.players[i - 1])
  end
  table.sort(tbl, comp_player)
  return tbl
end
