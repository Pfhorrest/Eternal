CollectionsUsed = { 22 }

precipitation_type = "rocks"
precipitation_count = 512
precipitation_phase = 1
precipitation_gravity = 1/4
precipitation_wind = 0
fogtimer = 420
pitch = .5
red = .125
green = 0
blue = .125
depth = 42
darken = false

scenery_cleared = false

function build_pool()
   Level._pool = {}

   for i=1,1024 do
      s = Scenery.new(0, 0, 0, 0, "gun")
      s._fake = true
   end

   for i=1,precipitation_count do
      local x, y, z, p = uniform.xyz_in_triangle_list(Level._triangles)
      table.insert(Level._pool, Scenery.new(x, y, z, p, precipitation_type))
   end

   for s in Scenery() do
      if s._fake then
	 s:delete()
      end
   end
end

function levelfog()
	fogtimer = fogtimer - 1

	if darken then
		if blue > .125 then
			red = red - 0.0005
			blue = blue - 0.0005
			depth = depth - 0.05
		else
			darken = false
		end
	elseif blue < .25 then
		red = red + 0.0005
		blue = blue + 0.0005
		depth = depth + 0.05
	else
		darken = true
	end

	if fogtimer > 30 then
		Level.fog.color.r = red
		Level.fog.color.g = green
		Level.fog.color.b = blue
		Level.fog.depth = depth
	else
		mult = 1 + (fogtimer * (300 + Game.random(300)) / 6300)
		Level.fog.color.r = blue * mult
		Level.fog.color.g = ((blue * mult) - blue) * .7
		Level.fog.color.b = blue * mult
		Level.fog.depth = depth
        if fogtimer == 7 then
            pitch = (200 + Game.random(200)) / 300
            for p in Players() do
                p:play_sound("surface explosion", pitch)
            end
        end
    end

	if fogtimer == 0 then
		fogtimer = 420 + Game.random(666)
	end
end

Triggers = {}

function Triggers.got_item(type, player)

	if type == "pistol" then
		if player.items["pistol"] > 2 then
			player.items["pistol"] = 2
			player.items["pistol ammo"] = player.items["pistol ammo"] + 1
		end

	elseif type == "fusion pistol" then
		if player.items["fusion pistol"] > 1 then
			player.items["fusion pistol"] = 1
			player.items["pistol ammo"] = player.items["pistol ammo"] + 1
		end

	elseif type == "assault rifle" then
		if player.items["assault rifle"] > 1 then
			player.items["assault rifle"] = 1
			player.items["assault rifle ammo"] = player.items["assault rifle ammo"] + 1
		end

	elseif type == "missile launcher" then
		if player.items["missile launcher"] > 1 then
			player.items["missile launcher"] = 1
			player.items["missile launcher ammo"] = player.items["missile launcher ammo"] + 1
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] + 1
		end

	elseif type == "alien weapon" then
		if player.items["alien weapon"] > 1 then
			player.items["alien weapon"] = 1
			player.items["shotgun ammo"] = player.items["shotgun ammo"] + 1
		end

	elseif type == "flamethrower" then
		if player.items["flamethrower"] > 1 then
			player.items["flamethrower"] = 1
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] + 1
		end

	elseif type == "shotgun" then
		if player.items["shotgun"] > 2 then
			player.items["shotgun"] = 2
			player.items["smg ammo"] = player.items["smg ammo"] + 1
		end

	elseif type == "smg" then
		if player.items["smg"] > 1 then
			player.items["smg"] = 1
			player.items["smg ammo"] = player.items["smg ammo"] + 1
		end

	elseif type == "assault rifle ammo" then
		if player.items["assault rifle"] < 1 then
			player.items["assault rifle ammo"] = player.items["assault rifle ammo"] - 1
			player.items["assault rifle"] = 1
		end

	elseif type == "assault rifle grenades" then
		if player.items["missile launcher"] < 1 then
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] - 1
			player.items["missile launcher"] = 1
		end

	elseif type == "missile launcher ammo" then
		if player.items["missile launcher"] < 1 then
			player.items["missile launcher ammo"] = player.items["missile launcher ammo"] - 1
			player.items["missile launcher"] = 1
		else
			player.items["assault rifle grenades"] = player.items["assault rifle grenades"] + 1
		end

	elseif type == "shotgun ammo" then
		if player.items["alien weapon"] < 1 then
			player.items["shotgun ammo"] = player.items["shotgun ammo"] - 1
			player.items["alien weapon"] = 1
		end

	elseif type == "flamethrower ammo" then
		if player.items["flamethrower"] < 1 then
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] - 1
			player.items["flamethrower"] = 1
		end

	elseif type == "fusion pistol ammo" then
		player.items["fusion pistol ammo"] = 0
		player.items["pistol ammo"] = player.items["pistol ammo"] + 1
	
	elseif type == "alien weapon ammo" then
		player.items["alien weapon ammo"] = 0
		if player.items["flamethrower ammo"] > player.items["shotgun ammo"] then
			player.items["shotgun ammo"] = player.items["shotgun ammo"] + 1
		else
			player.items["flamethrower ammo"] = player.items["flamethrower ammo"] + 1
		end
	end
end

function Triggers.projectile_created(projectile)
    projectile._original_owner = projectile.owner
end

function Triggers.player_damaged(victim, aggressor_player, aggressor_monster, damage_type, damage_amount, projectile)
    if projectile and projectile.type == "shotgun bullet" and projectile._original_owner == victim.monster then
        victim.life = victim.life + damage_amount
    end
end

function Triggers.init(restoring_game)
    for s in Scenery() do
        s:delete()
    end
end

function Triggers.pattern_buffer()
   for s in Scenery() do
	s:delete()
   end
   scenery_cleared = true
end

function Triggers.init()
   local polygon_list = {}
   for p in Polygons() do
      if p.ceiling.transfer_mode == "landscape" then
	 table.insert(polygon_list, p)
      end
   end
   Level._triangles = uniform.build_triangle_list(polygon_list)
   build_pool()
end

function Triggers.idle()
   if scenery_cleared == true then
	build_pool()
	scenery_cleared = false
   end
   local pool = Level._pool
   local position = pool[1].position
   local phase = precipitation_phase
   local gravity = phase * precipitation_gravity
   local wind = phase * precipitation_wind
   local phase_match = Game.ticks % phase
   for i = 1,precipitation_count do
      if i % phase == phase_match then
	 local e = pool[i]
	 position(e, e.x - wind, e.y - wind, e.z - gravity, e.polygon)
	 if e.z < e.polygon.floor.height then
	    local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
	    e:position(x, y, p.ceiling.height, p)
	 elseif e.z > e.polygon.ceiling.height then
	    local x, y, p = uniform.xy_in_triangle_list(Level._triangles)
	    e:position(x, y, p.floor.height, p)
	 end
      end
   end

   levelfog()
end